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

//compile with gcc -std=c99 -o readKeyd readKeyd.c -lcurl

void sendString(char data[], int length){
	char url[200] = "http://192.168.7.77/fheME/fheME/Einkaufszettel/addToList.php?data="; //change this to fit your needs
	strcat(url, data);
	CURL *curl;

	curl = curl_easy_init();
	if(curl) {
		curl_easy_setopt(curl, CURLOPT_URL, url);
		curl_easy_perform(curl);
		curl_easy_cleanup(curl);
	}
}

int main (int argc, char *argv[]){
	struct input_event ev[64];
	int fd, rd, value, size = sizeof (struct input_event);
	char name[256] = "Unknown";
	char *device = NULL;
	
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
		printf ("%s is not a vaild device. Exiting...\n", device);
		exit(0);
	}

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
		
		if(ev[1].code > 12 && ev[1].code != 28)
			continue;
		
		if(value == ' ' || ev[1].value != 1 || ev[1].type != 1)
			continue;
		
		if(ev[1].code == 28){ //Enter
			if(cursor == 0)
				continue;

			buffer[cursor] = '\0';
			
			sendString(buffer, cursor);
			
			cursor = 0;
		} else {
			int zahl = ev[1].code - 1;
			
			if(zahl == 10) //zero
				zahl = 0;
			
			zahl += 48; //make ASCII
			
			buffer[cursor] = zahl;
			cursor++;
		}
	}

	return 0;
}
