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

//устанавливаем соединение с базой данных
$dbh = new PDO('mysql:host=localhost;dbname=test;charset=UTF8', 'root', 'y@3#XUC5');

//создаем таблицу авторов
$authorsTableName = 'authors';
$authorsTableSql = <<<EOL
create table if not exists {$authorsTableName}
(
    id int(11) not null auto_increment,
    name varchar(255) not null,
    PRIMARY KEY(id)
) CHARACTER SET utf8 COLLATE utf8_general_ci;
EOL;
if ($dbh->exec($authorsTableSql) === false) {
    throw new Exception('Не удалось создать таблицу авторов');
}

//очищаем старые данные и добавляем несколько авторов для проверки
// Чтобы при очистке старых данных он сбрасывался, нужно вместо DELETE делаать TRUNCATE
$dbh->exec('TRUNCATE TABLE ' . $authorsTableName);
// Тогда и незачем писать id вручную -- auto_increment же
$dbh->exec('insert into ' . $authorsTableName . ' (name) values ("А. Азимов")');
$dbh->exec('insert into ' . $authorsTableName . ' (name) values ("Р. Брэдбери")');

//создаем таблицу с книгами
$booksTableName = 'books';
$booksTableSql = <<<EOL
create table if not exists {$booksTableName}
(
    id int(11) not null auto_increment,
    author_id int(11) not null,
    title varchar(255) not null,
    isbn varchar(255) not null,
    is_in_stock int(1) not null,
    PRIMARY KEY(id)
) CHARACTER SET utf8 COLLATE utf8_general_ci;
EOL;
if ($dbh->exec($booksTableSql) === false) {
    throw new Exception('Не удалось создать таблицу книг');
}

//очищаем старые данные и добавляем несколько книг для проверки
$dbh->exec('delete from ' . $booksTableName);
$dbh->exec('insert into ' . $booksTableName . ' (author_id, title, isbn, is_in_stock) values (1, "Я, робот", "5-699-13798-5", 1)');
$dbh->exec('insert into ' . $booksTableName . ' (author_id, title, isbn, is_in_stock) values (2, "Вино из одуванчиков", "978-5-699-37382-6", 0)');
?>


<?php
// MySQL 8.0.16 (legacy authentication), PHP 7.1.19
// решение в этом блоке

// добавляет в html-документ строки таблицы
function getTableRows($dbh, $booksTableName, $authorsTableName){
    $joinTablesSql = <<<EOL
    SELECT *
    FROM
        {$booksTableName}
        LEFT OUTER JOIN 
        {$authorsTableName}
            ON {$booksTableName}.author_id = {$authorsTableName}.id;
EOL;

    $result = "";
    foreach ($dbh->query($joinTablesSql) as $row) {
        $isInStock = $row['is_in_stock'] ? "Да" : "Нет";
        // возможно будет полезным сделать так на случай, если автора этой книги не окажется в таблице авторов.
        // не знаю, правда, насколько возможен такой случай
        $name = $row['name'] ?? "Н/Д";

        $result .=  "<tr>" .
            "<td>{$row['title']}</td>" .
            "<td>{$row['isbn']}</td>" .
            "<td>{$name}</td>" .
            "<td>{$isInStock}</td>" .
            "</tr>";
    }
    return $result;
}

$tableRows = getTableRows($dbh, $booksTableName, $authorsTableName);
?>

<table>
    <tr>
        <th class="table">Название</th>
        <th class="table">ISBN</th>
        <th class="table">Автор</th>
        <th class="table">В наличии</th>
    </tr>
    <?=$tableRows?>
</table>