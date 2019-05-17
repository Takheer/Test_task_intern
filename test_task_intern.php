<?php
/*
    Требуется создать базу данных с книгами на складе книжного магазина
    и вывести список книг в виде таблицы.

    Для решения этой задачи был подготовлен небольшой скрипт, который
    создает структуру базы данных.

    Необходимо дописать получение книг из базы и вывод соответствующего
    списку книг html, используя php и mysql.

    Допускаются любые изменения в скрипте, но не в структуре базы данных,
    которая задается изначальными запросами.
*/

error_reporting(E_ALL);
//устанавливаем соединение с базой данных
$password = file("/Library/WebServer/Documents/password.cnf", FILE_IGNORE_NEW_LINES)[0];
$dbh = new PDO('mysql:host=localhost;dbname=test;charset=UTF8', 'root', $password);

//создаем таблицу авторов
// Убрал $authorTableName и $booksTableName. Всё равно названия таблиц скорее всего не поменяются,
// зато читаемость кода стала лучше в разы
$authorsTableSql = <<<EOL
CREATE TABLE IF NOT EXISTS authors
(
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL ,
    PRIMARY KEY(id)
) CHARACTER SET utf8 COLLATE utf8_general_ci;
EOL;
if ($dbh->exec($authorsTableSql) === false) {
    throw new Exception('Не удалось создать таблицу авторов');
}

//очищаем старые данные и добавляем несколько авторов для проверки
// Чтобы при очистке старых данных он сбрасывался, нужно вместо DELETE делаать TRUNCATE
$dbh->exec('TRUNCATE TABLE authors');
// Тогда и незачем писать id вручную -- auto_increment же
$dbh->exec('INSERT INTO authors (name) VALUE ("А. Азимов")');
$dbh->exec('INSERT INTO authors (name) VALUE ("Р. Брэдбери")');

//создаем таблицу с книгами
$booksTableSql = <<<EOL
CREATE TABLE IF NOT EXISTS books
(
    id INT(11) NOT NULL AUTO_INCREMENT,
    author_id INT(11) NOT NULL ,
    title VARCHAR(255) NOT NULL,
    isbn VARCHAR(255) NOT NULL,
    is_in_stock INT(1) NOT NULL,
    PRIMARY KEY(id)
) CHARACTER SET utf8 COLLATE utf8_general_ci;
EOL;
if ($dbh->exec($booksTableSql) === false) {
    throw new Exception('Не удалось создать таблицу книг');
}

//очищаем старые данные и добавляем несколько книг для проверки
$dbh->exec('TRUNCATE TABLE books');
$dbh->exec('INSERT INTO books (author_id, title, isbn, is_in_stock) VALUES (1, "Я, робот", "5-699-13798-5", 1)');
$dbh->exec('INSERT INTO books (author_id, title, isbn, is_in_stock) VALUES (2, "Вино из одуванчиков", "978-5-699-37382-6", 0)');
?>


<?php
// MySQL 8.0.16 (legacy authentication), PHP 7.1.19
// решение в этом блоке

// делает запрос в базу данных и возвращает содержимое в виде объекта PDOStatement
function getTableRowsFromDB($dbh){
    $jointTablesSql = <<<EOL
    SELECT *
    FROM
        books
        LEFT OUTER JOIN 
        authors
            ON books.author_id = authors.id;
EOL;

    return $dbh->query($jointTablesSql);
}
?>


<html>
<head>
    <style>
        table, th, td{
            border: 1px solid grey;
            border-collapse: collapse;
            padding: 5px;
        }
    </style>
</head>
<body>
<table>
    <tr>
        <th>Название</th>
        <th>ISBN</th>
        <th>Автор</th>
        <th>В наличии</th>
    </tr>
    <?php foreach (getTableRowsFromDB($dbh) as $row) : ?>
    <tr>
        <td><?php echo $row['title'] ?></td>
        <td><?php echo $row['isbn'] ?></td></td>
        <td><?php echo $row['name'] ?? "Н/Д" ?></td>
        <td><?php echo $row['is_in_stock'] ? "Да" : "Нет" ?></td>
    </tr>
    <?php endforeach; ?>
</table>
</body>
</html>