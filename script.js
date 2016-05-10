function sayHello() 
{
	alert("Hello");
}

function showAnotherAnnouncements(tableClass)
{
	//Tabella su cui visualizzare ulteriori annunci.
	//Sono in totale max 45, 15 alla volta.
	var table = document.getElementsByClassName(tableClass)[0];
	var displayedRowsCount = 0; // Numero di annunci resi visibili, max 15 a click.

	//Controllo che sia stata trovata la tabella.
	if(table == undefined)
	{
		//Restituisco un errore nel caso non abbia trovato la tabella.
		console.log("Tabella " + tableClass + " non trovata.\nImpossibile aggiornare la lista degli ultimi annunci.");
		return;
	}
	
	//Scorro tutte le righe:
	for(var i = 0; i < table.rows.length; i++)
	{
		//Controllo se la riga abbia lo style.display a 'none' e se posso ancora visualizzarne le visualizzo.
		if(table.rows[i].style.display == "none" && displayedRowsCount <= 15)
		{
			//Cambio lo style.display a 'table-row' così diventa visibile.
			table.rows[i].style.display = 'table-row';
			displayedRowsCount++; //Aumento il contatore.
		}
	}
}