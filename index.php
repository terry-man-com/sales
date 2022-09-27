<?php

require_once __DIR__ . '/functions.php';

// データベースに接続
$dbh = connect_db();
$year = '';
$branch = '';
$staff = '';
// getのデータの受け取り
$year = filter_input(INPUT_GET, 'year');
$branch = filter_input(INPUT_GET, 'branch');
$staff = filter_input(INPUT_GET, 'staff');
// 支店データの取得
$sql = 'SELECT name FROM branches ORDER BY id';
$stmt = $dbh->prepare($sql);
$stmt->execute();
$branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
// 従業員データの取得
$sql = 'SELECT name FROM staffs ORDER BY id';
$stmt = $dbh->prepare($sql);
$stmt->execute();
$staffs = $stmt->fetchAll(PDO::FETCH_ASSOC);
// SQL文の組み立て
if(!empty($year) && !empty($branch) && !empty($staff)){
    $sql = <<<EOM
SELECT
    A.year,A.month,A.b_name,A.st_name,A.sale
FROM 
    (SELECT s.year, s.month, b.name b_name, st.name st_name, sale, b.id b_id, st.id st_id, s.id s_id
    FROM branches b 
    INNER JOIN staffs st 
    ON b.id = st.branch_id
    INNER JOIN sales s
    ON st.id = s.staff_id
)A
where year = $year and b_name = "$branch" and st_name = "$staff"
ORDER BY A.year, A.month, A.b_id, A.st_id
EOM;
} elseif (!empty($year) && !empty($branch)) {
    $sql = <<<EOM
SELECT
    A.year,A.month,A.b_name,A.st_name,A.sale
FROM 
    (SELECT s.year, s.month, b.name b_name, st.name st_name, sale, b.id b_id, st.id st_id, s.id s_id
    FROM branches b 
    INNER JOIN staffs st 
    ON b.id = st.branch_id
    INNER JOIN sales s
    ON st.id = s.staff_id
)A
where year = $year and b_name = "$branch" 
ORDER BY A.year, A.month, A.b_id, A.st_id
EOM;
} elseif (!empty($year) && !empty($staff)) {
    $sql = <<<EOM
SELECT
    A.year,A.month,A.b_name,A.st_name,A.sale
FROM 
    (SELECT s.year, s.month, b.name b_name, st.name st_name, sale, b.id b_id, st.id st_id, s.id s_id
    FROM branches b 
    INNER JOIN staffs st 
    ON b.id = st.branch_id
    INNER JOIN sales s
    ON st.id = s.staff_id
)A
where year = $year and st_name = "$staff"
ORDER BY A.year, A.month, A.b_id, A.st_id
EOM;
} elseif (!empty($year)) {
    $sql = <<<EOM
SELECT
    A.year,A.month,A.b_name,A.st_name,A.sale
FROM 
    (SELECT s.year, s.month, b.name b_name, st.name st_name, sale, b.id b_id, st.id st_id, s.id s_id
    FROM branches b 
    INNER JOIN staffs st 
    ON b.id = st.branch_id
    INNER JOIN sales s
    ON st.id = s.staff_id
)A
where year = $year
ORDER BY A.year, A.month, A.b_id, A.st_id
EOM;
} else {
    $sql = <<<EOM
SELECT
    A.year,A.month,A.b_name,A.st_name,A.sale
FROM 
    (SELECT s.year, s.month, b.name b_name, st.name st_name, sale, b.id b_id, st.id st_id, s.id s_id
    FROM branches b 
    INNER JOIN staffs st 
    ON b.id = st.branch_id
    INNER JOIN sales s
    ON st.id = s.staff_id
)A
ORDER BY A.year, A.month, A.b_id, A.st_id
EOM;
}
$stmt = $dbh->prepare($sql);
$stmt->execute();
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);
// 売上合計計算
$sales_total = number_format(array_sum(array_column($sales, 'sale')));
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>sales_list</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://unpkg.com/ress/dist/ress.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Aclonica&family=M+PLUS+1p&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="wrapper">
        <h1 class="title">売上一覧</h1>
        <div class="form-area">
            <form action="" method="get">
                <div class="select-area">
                    <label for="year">年</label>
                    <input type="number" id="year" name="year" min="2017" max="2022" value="<?= h($year) ?>">
                    <label for="branch">支店</label>
                    <select name="branch" id="branch" class="branches">
                        <option value=""></option>
                        <?php foreach ($branches as $b) : ?>
                            <?php if ($b['name'] == $branch) : ?>
                                <option value="<?= h($b['name']) ?>" selected><?= h($b['name']) ?></option>
                            <?php else : ?>
                                <option value="<?= h($b['name']) ?>"><?= h($b['name']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                    <label for="staff">従業員</label>
                    <select name="staff" id="staff" class="staffs">
                        <option value=""></option>
                        <?php foreach ($staffs as $s) : ?>
                            <?php if ($s['name'] == $staff) : ?>
                                <option value="<?= h($s['name']) ?>" selected><?= h($s['name']) ?></option>
                            <?php else : ?>
                                <option value="<?= h($s['name']) ?>"><?= h($s['name']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="btn-area">
                    <input type="submit" value="検索" class="btn btn-search">
                </div>
            </form>
        </div>
        <div class="list-wrapper">
            <table class="list-area">
                <thead>
                    <tr>
                        <th class="sales-year">年</th>
                        <th class="sales-month">月</th>
                        <th class="sales-branch">支店</th>
                        <th class="sales-staff">従業員</th>
                        <th class="sales-price">売上</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales as $s) : ?>
                        <tr>
                            <td><?= h($s['year']) ?></td>
                            <td><?= h($s['month']) ?></td>
                            <td><?= h($s['b_name']) ?></td>
                            <td><?= h($s['st_name']) ?></td>
                            <td><?= h($s['sale']) ?></td>
                        </tr>
                    <? endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="total-price">合計:<?= h($sales_total) ?>円</div>
    </div>
</body>

</html>