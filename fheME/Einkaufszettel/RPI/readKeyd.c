#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <errno.h>
#include <fcntl.h>
#include <dirent.h>
#include <linux/input.h>
#include <sys/types.h>
#include <sys/stat.h>
#include <sys/select.h>
#include <sys/time.h>
#include <termios.h>
#include <signal.h>
#include <curl/curl.h>
#include <curl/easy.h>
#include <libconfig.h>
#include <syslog.h>

//sudo aptitude install libcurl4-openssl-dev libconfig-dev
//compile with gcc -std=c99 -o readKeyd readKeyd.c -lcurl -lconfig -Wall


struct MemoryStruct {
  char *memory;
  size_t size;
};


static size_t writeMemoryCallback(void *contents, size_t size, size_t nmemb, void *userp) {
	char *message = (char *) contents;
	
	//printf("Response: %s\n", message);
	size_t realsize = size * nmemb;
	
	if(strcmp(message,"OK") != 0)
		syslog(LOG_ERR, "%s", message);
	
	//extern FILE *popen();
	//extern FILE *pclose();
	//FILE *fp;
	//int status;
	//char path[1035];
	//fp = popen("/usr/bin/play ok.wav -q -t alsa", "r");
	//fp = popen("/usr/bin/mpg123 -o pulse ok.mp3", "r");
	/*if (fp == NULL) {
		printf("Failed to run command\n" );
		exit;
	}*/
	/* Read the output a line at a time - output it. */
	//while (fgets(path, sizeof(path)-1, fp) != NULL)
	//	printf("%s", path);
	
	//pclose(fp);
	/*struct MemoryStruct *mem = (struct MemoryStruct *)userp;

	mem->memory = realloc(mem->memory, mem->size + realsize + 1);
	if (mem->memory == NULL) {
		printf("not enough memory (realloc returned NULL)\n");
		exit(EXIT_FAILURE);
	}

	memcpy(&(mem->memory[mem->size]), contents, realsize);
	mem->size += realsize;
	mem->memory[mem->size] = 0;

	printf("Response: %s\n", mem->memory);
	*/
	return realsize;
}

void sendString(const char *url, char data[], struct MemoryStruct *returnData){
	char tempUrl[200] = "";
	strcpy(tempUrl, url);
	
	strcat(tempUrl, data);
	CURL *curl;

	syslog(LOG_ALERT, "Scanned %s", data);

	curl = curl_easy_init();
	if(curl) {
		//printf("Calling %s...\n", tempUrl);
		
		curl_easy_setopt(curl, CURLOPT_URL, tempUrl);
		curl_easy_setopt(curl, CURLOPT_WRITEFUNCTION, writeMemoryCallback);
		curl_easy_setopt(curl, CURLOPT_WRITEDATA, returnData);
		curl_easy_perform(curl);
		curl_easy_cleanup(curl);
	}
}

int main (int argc, char *argv[]){
	struct input_event ev[64];
	int fd, rd, value, size = sizeof (struct input_event);
	char name[256] = "Unknown";
	char *device = NULL;
	const char *url;
	struct MemoryStruct returnData;
	
	openlog("readKeyd", LOG_CONS | LOG_PID, LOG_LOCAL3);

	config_t cfg;
	config_init(&cfg);
	
    /* Read the file. If there is an error, report it and exit. */
    if (!config_read_file(&cfg, "/etc/readKeyd/readKeyd.conf")) {
        printf("%s:%d - %s\n", config_error_file(&cfg), config_error_line(&cfg), config_error_text(&cfg));
        config_destroy(&cfg);
        exit(0);
    }
	
    if (!config_lookup_string(&cfg, "URL", &url))
        printf("No 'URL' setting in configuration file.\n");
	
	if (argv[1] == NULL){
		printf("Please specify (on the command line) the path to the dev event interface device\n");
		exit(0);
	}

	if ((getuid()) != 0){
		printf ("You are not root! This will not work. Exiting...\n");
		exit(0);
	}
	
	if (argc > 1)
		device = argv[1];

	if ((fd = open(device, O_RDONLY)) == -1){
		printf ("%s is not a valid device. Exiting...\n", device);
		exit(0);
	}

	//grab device exclusively
	ioctl(fd, EVIOCGRAB, 1);
	//Print Device Name
	ioctl(fd, EVIOCGNAME (sizeof (name)), name);
	//printf("Reading From : %s (%s)\n", device, name);
	
	char buffer[50];
	int cursor = 0;
	
	while (1){
		if ((rd = read (fd, ev, size * 64)) < size){
			perror("read() failed. Exiting...\n");
			exit(0);
		}
		
		value = ev[0].value;
		
		//if(ev[1].code > 12 && ev[1].code != 28)
		//	continue;
		
		if(value == ' ' || ev[1].value != 1 || ev[1].type != 1)
			continue;
		
		if(ev[1].code == 28){ //Enter
			if(cursor == 0)
				continue;

			buffer[cursor] = '\0';
			
			sendString(url, buffer, &returnData);
			//printf("Send : %s\n", buffer);
			cursor = 0;
		} else {
			int zahl = ev[1].code;

			//printf("To : %d\n", zahl);

			char c [3];
			int n;
			n = sprintf(c, "%d", zahl);
			//printf("No : %d\n", n);
			//printf("St : %s\n", c);
			
			buffer[cursor] = c[0];

			if(n>=1)
				buffer[cursor+1] = c[1];

			if(n>=2)
				buffer[cursor+2] = c[2];
			//buffer[cursor+2] = c[9];
			buffer[cursor+n] = ';';
			cursor += n+1;
		}
	}

	return 0;
}
