

function changeSelectValue(fieldID) {

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

function changeTextValue(fieldID) {
    let sel = document.getElementById(fieldID);
    document.getElementById(fieldID +'_title').value = sel.options[sel.selectedIndex].text;
}