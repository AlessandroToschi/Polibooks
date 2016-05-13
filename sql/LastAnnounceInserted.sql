CREATE PROCEDURE `new_procedure` ()
BEGIN

SELECT books.title, books.authors, books.year, campus.name, books_user.price, books_user.quality, books_user.publish_date, books_user.id
FROM books_user
INNER JOIN users_campus ON books_user.user_id = users_campus.user_id
INNER JOIN campus ON users_campus.campus_id = campus.id
INNER JOIN books on books_user.book_id = books.id
WHERE books_user.status = 1
ORDER BY books_user.publish_date ASC
LIMIT 45;

END
