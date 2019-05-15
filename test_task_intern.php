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
error_reporting(-1);
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
$dbh->exec('delete from ' . $authorsTableName);
$dbh->exec('insert into ' . $authorsTableName . ' (id, name) values (1, "А. Азимов")');
$dbh->exec('insert into ' . $authorsTableName . ' (id, name) values (2, "Р. Брэдбери")');

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
$dbh->exec('insert into ' . $booksTableName . ' (author_id, title, isbn, is_in_stock) values (3, "Зелёный слоник", "777-5-699-37382-6", 0)');
?>


<?php
//решение в этом блоке

//возвращает имя автора из таблицы авторов по id, взятому из таблицы книг
function getAuthorNameById($dbh, $authorsTableName, $id){
    $authorNameSql = 'select id, name from ' . $authorsTableName . ' where id = ' . $id;
    $response = $dbh->prepare($authorNameSql);
    $response->execute();

    $result = $response->fetch(PDO::FETCH_ASSOC);
    return $result['name'] ?? 'Н/Д';
}

// приводит значение соответствующей колонки к человекочитаемому виду
function isInStock($flag){
    return $flag ? 'Да' : 'Нет';
}

//добавляет в html-документ строки таблицы
function echoTableRows($dbh, $booksTableName, $authorsTableName){
    $tableRowSql = 'select * from ' . $booksTableName;
    foreach ($dbh->query($tableRowSql) as $row) {
        echo '<tr>';
        echo '<td class="tableBorder">' . $row['title'] .
            '<td class="tableBorder">' . $row['isbn'] . '</td>' .
            '<td class="tableBorder">' . getAuthorNameById($dbh, $authorsTableName, $row['author_id']) . '</td>' .
            '<td class="tableBorder">' . isInStock($row['is_in_stock']) . '</td>';
        echo '</tr>';
    }
}
?>

<html>
<head>
    <style>
        .tableBorder{
            border: 1px solid black;
        }
    </style>
</head>
<body>
<table class="tableBorder">
    <tr>
        <th class="table">Название</th>
        <th class="table">ISBN</th>
        <th class="table">Автор</th>
        <th class="table">В наличии</th>
    </tr>
    <?php
        echoTableRows($dbh, $booksTableName, $authorsTableName);
    ?>
</table>
</body>
</html>


