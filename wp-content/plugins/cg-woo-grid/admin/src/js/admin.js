
/**
 * Copy the current value of the given select into the input text '#fieldID'_title
 * 
 * @param {String} fieldID the field ID
 * 
 */
function _changeTextValue(fieldID = null){

    if(fieldID) {
        let sel = document.getElementById(fieldID);
        document.getElementById(fieldID +'_title').value = sel.options[sel.selectedIndex].text;
    }

}

/**
 * Copy the current value of the given text field into the select field '#fieldID'
 * 
 * @param {String} fieldID  the field ID
 */
function _changeSelectValue(fieldID = null){

    if(fieldID) {
        console.log('changeSelectValue')
        console.log(fieldID)

        let newValue = document.getElementById(fieldID +'_title').value;
        let sel = document.getElementById(fieldID);
        let str = sel.options[sel.selectedIndex].value;

        var regex = /("(.*?)")/g;
        var matches = str.match(regex);

        if (matches) {
            var lastMatch = matches[matches.length - 1];
            str = str.replace( lastMatch,'"'+newValue+'"');
            console.log(str)
            sel.options[sel.selectedIndex].value = str
        }
    }

}


window.CDGADMIN = {
    changeTextValue: _changeTextValue,
    changeSelectValue: _changeSelectValue
};
