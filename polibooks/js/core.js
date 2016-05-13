var booksCounter = 0, searchCounter=0;
var searchTimeout = null;
var lastIsbn=0;

$(function(){
	$('#note').jqEasyCounter({
    	'maxChars': 300,
    	'maxCharsWarning': 280,
   		'msgFontSize': '12px',
    	'msgFontColor': '#000',
    	'msgFontFamily': 'Arial',
    	'msgTextAlign': 'left',
    	'msgWarningColor': '#F00',
    	'msgAppendMethod': 'insertBefore'              
	});

	$("#isbn").on('input',checkISBN);
	$("#isbn").keyup(checkISBN);
	$("#isbn").blur(checkISBN);
	$("#quality").blur(checkISBN);
	$("#quality").change(checkISBN);
	$("#price").blur(checkISBN);
	$("#price").keyup(checkISBN);
});

function changeState(n1,n2,q) {
    if(q == 1) {
        $(n1).removeClass("has-error").addClass("has-success");
        $(n2).removeClass("glyphicon-remove").addClass("glyphicon-ok");
    } else if(q == 0) {
        $(n1).removeClass("has-success");
        $(n2).removeClass("glyphicon-ok").removeClass("glyphicon-remove");
    } else {
        $(n1).addClass("has-error");
        $(n2).addClass("glyphicon-remove");
    }
}

function checkISBN()
{
	isbn = $("#isbn").val();
	if(isValidISBN13(isbn) || isValidISBN10(isbn)) {
        changeState("#isbng", "#isbnc", 1);
		
		if(lastIsbn!=isbn)
		{
			$(".selldiv").html( $("#divLoadingBar").html() );
			$.get("/polibooks/ajax/bookinfo.php", {"isbn":isbn}, function(data){
				$(".selldiv").html(data);
			});
			lastIsbn=isbn;
		}
		s = $(".selldiv").slideDown();

        if(isPriceValid() && isQualityValid() && isNewbookValid())
            $("#contsell").prop('disabled', false);
        else
            $("#contsell").prop('disabled', true);
    } else {
        changeState("#isbng", "#isbnc", 0);

        if(isbn.length == 13) 
            changeState("#isbng", "#isbnc", 0);
		s = $(".selldiv").slideUp();
        $("#contsell").prop('disabled', true);
    }

    changeState("#priceg", "#pricec", isPriceValid()?1:($("#price").val().length>0)?-1:0);
    if(isQualityValid) 
        $("#qualityg").addClass("has-success");
    else
        $("#qualityg").removeClass("has-success");
    
}
function isNewbookValid()
{
	if ($("#newbookform").length > 0) 
	{
		if ($("#new_title").val() && $("#new_author").val() && $("#new_pages").val() && $("#new_year").val())
			return true;
		else 
			return false;
	}
	else 
		return true;
}

function isPriceValid()
{
	if($("#price").val().match('^([0-9]+([\.,][0-9][0-9]?)?)$'))
		return true;
	else
		return false;
}

function isQualityValid()
{
	if($("#quality").val()==null)
		return false;
	return true;
}

function isValidISBN10(ISBNumber){
	var a, i;
	if(ISBNumber.length != 10)
		return false;
 
	a = 0;
	for(i=0;i<10;i++)
		if (ISBNumber[i] == "X" || ISBNumber[i] == "x")
			a += 10*(10-i);
		else if ($.isNumeric(ISBNumber[i]))
			a += parseInt(ISBNumber[i]) * (10-i);
		else
			return false;
	return (a % 11 == 0);
}

function isValidISBN13(ISBNumber) {
    var check, i;

    if(ISBNumber.length != 13)
    	return false;
 
    ISBNumber = ISBNumber.replace(/[-\s]/g,'');
 
    check = 0;
    for (i = 0; i < 13; i += 2) {
      check += +ISBNumber[i];
    }
    for (i = 1; i < 12; i += 2){
      check += 3 * +ISBNumber[i];
    }
    return (check % 10 === 0);
}

function insertAtCaret(element, text) {
    if (document.selection) {
        element.focus();
        var sel = document.selection.createRange();
        sel.text = text;
        element.focus();
    } else if (element.selectionStart || element.selectionStart === 0) {
        var startPos = element.selectionStart;
        var endPos = element.selectionEnd;
        var scrollTop = element.scrollTop;
        element.value = element.value.substring(0, startPos) + text + element.value.substring(endPos, element.value.length);
        element.focus();
        element.selectionStart = startPos + text.length;
        element.selectionEnd = startPos + text.length;
        element.scrollTop = scrollTop;
    } else {
        element.value += text;
        element.focus();
    }
}

function updatePriceForm(bookID) 
{
	var table = document.getElementById("manageBooksTable");
	
	if(table == undefined)
	{
		console.log("Impossibile trovare la tabella della gestione libri.");
	}
	
	var priceCell = table.rows[1].cells[4];
	console.log(priceCell.innerHTML);
	
	var newInput = document.createElement('input');
	newInput.setAttribute("value",priceCell.innerHTML.replace("€ ",""))
	newInput.className = "form-control";
	newInput.style.textAlign = "center";
	priceCell.parentNode.insertBefore(newInput, priceCell);
	priceCell.parentNode.removeChild(priceCell);
}