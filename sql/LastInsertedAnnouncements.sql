SELECT books.title, books.authors, books.year, GROUP_CONCAT(campus.name), books_user.price, books_user.quality, books_user.publish_date, books_user.id
FROM (SELECT DISTINCT * FROM books_user ORDER BY books_user.publish_date DESC LIMIT 45) as books_user
INNER JOIN books ON books.id = books_user.book_id
INNER JOIN users_campus ON users_campus.user_id = books_user.user_id
INNER JOIN campus ON campus.id = users_campus.campus_id
GROUP BY books_user.id
ORDER BY books_user.id DESC;
