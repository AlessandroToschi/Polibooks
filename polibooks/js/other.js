LIMIT=15;
MAXRECENTCOUNT=3;
$(function(){
	function k(e)
	{
		c = e.keyCode ? e.keyCode : e.which;
		if(c==27) hidepopup();
	}
	$("body").keypress(k);
	$("#searchbox").keyup(function(e) {
		if(searchTimeout != null) clearTimeout(searchTimeout);
			searchTimeout = setTimeout(search,300);
	});
});

popupid=0;
function popup(title, text)
{
	$("#msgtit").html(title);
	$("#msgtext").html(text);
	$("#msgpop").slideDown(300);
	$("#blackbg").fadeIn(300);
}

function hidepopup()
{
	$("#msgpop").fadeOut(300);
	$("#blackbg").fadeOut(300);
	popupid=0;
	updateHash();
}

function showbook(id)
{
	popupid=id;
	$.get("/polibooks/showbook.php?id="+id, function(data)
	{
		updateHash();
		popup("Dettagli sul libro", data);
	});
}

function appendBooks(table,rows)
{
	$.each(rows, function(d,r) {
		$('#'+table).append('<tr id="'+table+'_'+r.id+'"><td><a href="javascript:void(0);" onclick="showbook('+r.id+');">'+r.title+'</a></td><td>'+r.author+'</td><td>'+r.year+'</td><td><input type="text" value="'+r.where+'" style="width: 120px; border:0; text-align: center; background-color:rgba(255,255,255,0);" readonly/></td><td>'+r.quality+'</td><td>&euro;'+r.price+'</td><td title="'+r.realdate+'">'+r.date+'</td></tr>');
	});
}

function buildTable(name)
{
	out = '<div class="table-responsive"><table class="table table-striped" id="'+name+'">';
	out+= '<tr><th>Titolo</th><th>Autore</th><th>Anno</th><th>Sede</th><th>Qualit&agrave;</th><th>Prezzo</th><th>Pubblicato il</th></tr>';
	out+= '</table></div>';
	return out;
}

function loadRecentBooks()
{
	$.getJSON("/polibooks/ajax/search.php?data=lastest&page="+booksCounter, function(data) {
		appendBooks('tblResult',data);
		showLoader(false);
		if(data.length==LIMIT && booksCounter<MAXRECENTCOUNT-1)
			$('#btnContinue').show();
	});
}

function showLoader(visible)
{
	if(visible) $('#loadingAnim').show();
	else $('#loadingAnim').hide();
}

function getHashFromUrl(url){
    return $("<a />").attr("href", url)[0].hash.replace(/^#/, "");
}

hashlock = false;
function updateHash()
{
	sx = $("#searchbox").val();

	hash="";
	if(sx!=null && sx.length>3) 
		hash="search="+encodeURIComponent(sx);

	if(popupid>0)
	{
		if(hash.length>0) hash+="&";
		hash+="show="+popupid;
	}

	window.location.hash = hash;
}

function updateMain()
{
	hash = getHashFromUrl(window.location);
	hx = hash.split("&");
	show = 0;
	$.each(hx, function(dumb, p){
		px = p.split("=");
		if(px[0]=='show') show=parseInt(px[1]);
	});

	if(show!=0)
		showbook(show);
	else
		hidepopup();

	$.getJSON("/polibooks/ajax/search.php?data=lastest", function(data) {
		$('#divBooks').html( buildTable('tblResult') );
		$('#divBooks').append('<a id="btnContinue" href="javascript:void(0);"><span class="glyphicon glyphicon-chevron-down" /></a>');
		if(data.length<LIMIT)
			$('#btnContinue').hide();

		$('#btnContinue').click(function() {
			booksCounter++;
			loadRecentBooks();
			$('#btnContinue').hide();
			showLoader(true);
		});
		appendBooks('tblResult', data);
		showLoader(false);
	});
}

function updateSearch()
{
	hash = getHashFromUrl(window.location);
	hx = hash.split("&");
	src = "";
	show = 0;
	$.each(hx, function(dumb, p){
		px = p.split("=");
		if(px[0]=='search') src=decodeURIComponent(px[1]);
		if(px[0]=='show') show=parseInt(px[1]);
	});

	if(show!=0)
		showbook(show);
	else
		hidepopup();

	if(src.length>0 && src!=$("#searchbox").val())
	{
		$("#searchbox").val(src);
		search();
	}
}

function loadSearch()
{
	updateSearch();
}
	
function loadMain()
{
	showLoader(true);
	updateMain();
}

function cSearch()
{
	text = $.trim($("#searchbox").val());
	$.getJSON("/ajax/search.php?text="+encodeURIComponent(text)+"&page="+searchCounter, function(data) {
		appendBooks('tblSearch',data);
		if(data.length == LIMIT)
			$('#btnCSearch').show();
		showLoader(false);
	});
}

function search()
{
	searchTimeout = null;
	text = $.trim($("#searchbox").val());
	
	if(CryptoJS.SHA1(text).toString(CryptoJS.enc.Hex)=="3977aff8c531e3be9008606f291bb75fc715a771")
	{
		$('#divSearch').html(CryptoJS.enc.Base64.parse('PGI+SGV5IGdlbml1cyEgJmx0OzM8L2I+').toString(CryptoJS.enc.Utf8));
		$('#divSearch').show();
	}
	else if(text.length>=3)
	{
		$('#divSearch').html("Searching..");
		$('#colvisib').fadeIn(300);
		$('#divSearch').fadeIn(300);
		showLoader(true);
		updateHash();
		searchCounter = 0;
		$.getJSON("/ajax/search.php?text="+encodeURIComponent(text), function(data) {
			$('#divSearch').html("");
			showLoader(false);
			if(data.length>0)
			{
				$('#divSearch').append( buildTable('tblSearch') );
				$('#divSearch').append('<a id="btnCSearch" href="javascript:void(0);"><span class="glyphicon glyphicon-chevron-down" /></a>');
				appendBooks('tblSearch',data);
				
				if(data.length == LIMIT)
				{
					$('#btnCSearch').show();
					$('#btnCSearch').click(function() {
						searchCounter++;
						cSearch();
						showLoader(true);
						$('#btnCSearch').hide();
					});
				}
				else
					$('#btnCSearch').hide();
			}
			else
				$('#divSearch').append("Nessun risultato trovato :(");
		});
	}
	else
	{
		$('#divSearch').fadeOut(300);
		$('#colvisib').fadeOut(300);
		updateHash();
	}	
}


