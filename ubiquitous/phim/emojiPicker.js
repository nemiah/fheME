var emojiPicker = {
	json: {},
	
	clearElement: function(el){
		el.innerHTML = ''
	},

	fetchJSON: function(path) {
		return fetch(path).then(response => {
			const ct = response.headers.get('content-type');
			
			if (ct && ct.includes('application/json'))
				return response.json();
			
			throw new TypeError('this is not json!');
		}).then(json => {
			return json;
		}).catch(console.log);
	},

	createLink: function(str, match, method) {
		const link = document.createElement('a');
		
		link.innerHTML = match.emoji;
		link.setAttribute('title', match.name);
		link.setAttribute('class', 'emoji');
		link.addEventListener('click', () => method(str, match.emoji));
		
		return link;
	},
	
	init: function(elementID){
		let matches;
		const events = ['input', 'keyup'];
		const input = document.getElementById(elementID);
		const picker = document.createElement('div');
		
		picker.setAttribute('class', 'picker');
		input.parentNode.appendChild(picker);

		const updateText = (last, emoji) => {
			input.value = input.value.replace(last, emoji);
			input.focus() & emojiPicker.clearElement(picker);
		};

		const updatePicker = (str, matches) => {
			emojiPicker.clearElement(picker) & matches.forEach(match => {
				picker.appendChild(emojiPicker.createLink(str, match, updateText));
			});
		};

		const find = (str, emoji) => {
			const match = new RegExp(`^${str.substring(1, str.length)}`)
			matches = Object
				.keys(emoji)
				.filter(emoj => match.test(emoj))
				.map(key => ({ name: key, emoji: emoji[key] }))
			return matches.length && updatePicker(str, matches)
		};
		
		events.map(function(event) {
			input.addEventListener(event, (event) => {
				const { value } = event.target;
				const lastWord = value.substring(value.lastIndexOf(' ') + 1, value.length);
				const match = /:[a-z0-9]/;
				
				if (!event.ctrlKey && event.keyCode === 13)
					emojiPicker.clearElement(picker);
				
				if (event.ctrlKey && event.keyCode === 13 && matches.length && lastWord.match(match))
					return updateText(lastWord, matches[0].emoji);
				
				return lastWord.match(match) ? find(lastWord, emojiPicker.json) : emojiPicker.clearElement(picker);
			});
		});
	}

};
emojiPicker.fetchJSON('./ubiquitous/phim/emojis.json').then(emoji => emojiPicker.json = emoji);