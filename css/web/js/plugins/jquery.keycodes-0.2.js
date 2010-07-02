/**
	Charcode lookup by Ramblingwood
	http://ramblingwood.com
*/
String.prototype.trim = function() {
	return this.replace(/^\s+|\s+$/g,"");
}
String.prototype.ltrim = function() {
	return this.replace(/^\s+/,"");
}
String.prototype.rtrim = function() {
	return this.replace(/\s+$/,"");
}
$.charcode = function(input, returnInt) {
	if(typeof(returnInt) == 'undefined')
		returnInt = false;
	if(typeof(input) == 'object')
		input = (input.keyCode ? input.keyCode : input.which);
	if(typeof(input) == 'string' || typeof(input) == 'number')
		input = input;
	var charcodes = {
		'backspace':'8','tab':'9','enter':'13','shift':'16','ctrl':'17','alt':'18','space':'32',
		'pause/break':'19','pause':'19','break':'19','caps lock':'20','escape':'27','page up':'33','page down':'34','end':'35',
		'home':'36','left arrow':'37','up arrow':'38','right arrow':'39','down arrow':'40','insert':'45',
		'delete':'46','0':'48','1':'49','2':'50','3':'51','4':'52',
		'5':'53','6':'54','7':'55','8':'56','9':'57','a':'65',
		'b':'66','c':'67','d':'68','e':'69','f':'70','g':'71',
		'h':'72','i':'73','j':'74','k':'75','l':'76','m':'77',
		'n':'78','o':'79','p':'80','q':'81','r':'82','s':'83',
		't':'84','u':'85','v':'86','w':'87','x':'88','y':'89',
		'z':'90','left window key':'91','right window key':'92','select key':'93','numpad 0':'96','numpad 1':'97',
		'numpad 2':'98','numpad 3':'99','numpad 4':'100','numpad 5':'101','numpad 6':'102','numpad 7':'103',
		'numpad 8':'104','numpad 9':'105','multiply':'106','add':'107','subtract':'109','decimal point':'110',
		'divide':'111','f1':'112','f2':'113','f3':'114','f4':'115','f5':'116',
		'f6':'117','f7':'118','f8':'119','f9':'120','f10':'121','f11':'122',
		'f12':'123','num lock':'144','scroll lock':'145','semi-colon':'186','equal sign':'187','equals sign':'187',
		'comma':'188','dash':'189','period':'190','forward slash':'191','grave accent':'192','open bracket':'219',
		'back slash':'220','close braket':'221','single quote':'222'
	};
	var ReverseCharcodes = {
		'8':'backspace','9':'tab','enter':'13','16':'shift','17':'ctrl','18':'alt','32':'space',
		'19':'pause/break','19':'pause','19':'break','20':'caps lock','27':'escape','33':'page up',
		'34':'page down','35':'end','36':'home','37':'left arrow','38':'up arrow','39':'right arrow',
		'40':'down arrow','45':'insert','46':'delete','48':'0','49':'1','50':'2',
		'51':'3','52':'4','53':'5','54':'6','55':'7','56':'8',
		'57':'9','65':'a','66':'b','67':'c','68':'d','69':'e',
		'70':'f','71':'g','72':'h','73':'i','74':'j','75':'k',
		'76':'l','77':'m','78':'n','79':'o','80':'p','81':'q',
		'82':'r','83':'s','84':'t','85':'u','86':'v','87':'w',
		'88':'x','89':'y','90':'z','91':'left window key','92':'right window key','93':'select key',
		'96':'numpad 0','97':'numpad 1','98':'numpad 2','99':'numpad 3','100':'numpad 4','101':'numpad 5',
		'102':'numpad 6','103':'numpad 7','104':'numpad 8','105':'numpad 9','106':'multiply','107':'add',
		'109':'subtract','110':'decimal point','111':'divide','112':'f1','113':'f2','114':'f3',
		'115':'f4','116':'f5','117':'f6','118':'f7','119':'f8','120':'f9',
		'121':'f10','122':'f11','123':'f12','144':'num lock','145':'scroll lock','186':'semi-colon',
		'187':'equal sign','187':'equals sign','188':'comma','189':'dash','190':'period','191':'forward slash',
		'192':'grave accent','219':'open bracket','220':'back slash','221':'close braket','222':'single quote'
	};
	if(returnInt === false && (typeof(input) == 'string'))
		return charcodes[input.toLowerCase()];
	if(returnInt === true && (typeof(input) == 'string'))
		return parseInt(charcodes[input.toLowerCase()]);
	if(typeof(input) == 'number') {
		return ReverseCharcodes[input];
	}
};
$.isKey = function(e,input) {
	var key = $.charcode(e,true);
	if(typeof(input) == 'string')
		input = input.split(',');
	if(typeof(input) == 'object') {
		var r = false;
		for(i in input) {
			if(input[i].trim() == key)
				r = true;
		}
		return r;
	}
	else {
		input = input.trim();
		return (input == key ? true : false);
	}
};
