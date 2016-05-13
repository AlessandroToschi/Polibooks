<html>
<head>
	<meta charset="utf-8">
	<title>Polibooks.it</title>
	<link href="style.css" rel="stylesheet" type="text/css">
	<script src="script.js"></script>
</head>
<body>
	<header>
		<nav class="NavigationHeader">
			<a class="NavigationHeaderItem" href="#">POLIBOOKS</a>
			<a class="NavigationHeaderItem" href="#">Chi siamo?</a>
			<a class="NavigationHeaderItem" href="#">Cerca un libro</a>
			<a class="NavigationHeaderItem" href="#">Vendi un libro</a>
			<a class="NavigationHeaderItem" style="float: right;" href="#">Login</a>
		</nav>
	</header>
	<main>
		<section class="MainContainer Centered">
				<h2 class="Centered">Ultimi libri aggiungi</h2>
				<table class="LastInsertedAnnouncementsTable">
					<thead>
						<tr style="display: table-row;">
							<th class="LastInsertedAnnouncementsTableHeader">Titolo</th>
							<th class="LastInsertedAnnouncementsTableHeader">Autori</th>
							<th class="LastInsertedAnnouncementsTableHeader">Anno</th>
							<th class="LastInsertedAnnouncementsTableHeader">Sede</th>
							<th class="LastInsertedAnnouncementsTableHeader">Prezzo</th>
							<th class="LastInsertedAnnouncementsTableHeader">Qualità</th>
							<th class="LastInsertedAnnouncementsTableHeader">Data pubblicazione</th>
						</tr>
					</thead>
					<tbody>
						<?php
						date_default_timezone_set('UTC');
				
						$db_connection = mysqli_connect('127.0.0.1', 'aleto','toscolino', 'Polibooks');
						
						if(!$db_connection)
						{
							die("Connection failed: ".mysqli_connect_error());
						}
						
						$query  = 'SELECT books.title, books.authors, books.year, GROUP_CONCAT(campus.name), books_user.price, books_user.quality, books_user.publish_date, books_user.id ';
						$query .= 'FROM (SELECT DISTINCT * FROM books_user ORDER BY books_user.publish_date DESC LIMIT 45) as books_user ';
						$query .= 'INNER JOIN books ON books.id = books_user.book_id ';
						$query .= 'INNER JOIN users_campus ON users_campus.user_id = books_user.user_id ';
						$query .= 'INNER JOIN campus ON campus.id = users_campus.campus_id ';
						$query .= 'GROUP BY books_user.id ';
						$query .= 'ORDER BY books_user.id DESC;';

						$query_result = mysqli_query($db_connection, $query) or die("Libir");
						$rows_count = 0;
						
						while($query_row = $query_result->fetch_row())
						{
							$row_visible = $rows_count > 15 ? 'style=\'display : none;\'' : 'style=\'display : table-row;\'';
							
							$table_row  = "<tr class=\'LastInsertedAnnouncementsTableRow\' onclick=\'sayHello()\' " . $row_visible . ">";
							$table_row .= '<td class=\'LastInsertedAnnouncementsTableData\'>'.utf8_encode($query_row[0]).'</td>';
							$table_row .= '<td class=\'LastInsertedAnnouncementsTableData\'>'.utf8_encode($query_row[1]).'</td>';
							$table_row .= '<td class=\'LastInsertedAnnouncementsTableData\'>'.$query_row[2].'</td>';
							$table_row .= '<td class=\'LastInsertedAnnouncementsTableData\'>'.$query_row[3].'</td>';
							$table_row .= '<td class=\'LastInsertedAnnouncementsTableData\'>'.number_format($query_row[4] / 100.0, 2, ',','').'€</td>';
							$table_row .= '<td class=\'LastInsertedAnnouncementsTableData\'>'.$query_row[5].'</td>';
							$table_row .= '<td class=\'LastInsertedAnnouncementsTableData\'>'.date('d/m/y',(time() - $query_row[6])).'</td>';
							echo $table_row;
							
							$rows_count++;
						}
						
						mysqli_close($db_connection);
						?>
					</tbody>
					<tfoot>
						<tr class="Centered">
							<td class="Centered"><button type="button" onclick="showAnotherAnnouncements('LastInsertedAnnouncementsTable')">alrt</button></td>
						</tr>
					</tfoot>
				</table>
			</div>
			
		</section>
	</main>
	<footer>
		<a href="#">Footer</a>
	</footer>
</body>
</html>