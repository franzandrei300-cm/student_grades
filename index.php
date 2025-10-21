<?php include 'db.php'; ?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Quarter Grades Activity (SQL + CRUD)</title>
  <style>
    body { font-family: Arial; margin:20px; background:#f7fbff; color:#072; }
    h1 { color:#2b6cb0; font-size:20px; margin-bottom:10px; }
    h3 { color:#2b6cb0; margin:0 0 5px; font-size:15px; }
    .insight-container { display:flex; flex-wrap:wrap; gap:10px; margin-bottom:20px; }
    .insight-box { background:white; padding:10px; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.1); min-width:180px; flex:1; }
    .card { background:white; padding:12px; border-radius:8px; box-shadow:0 1px 3px rgba(0,0,0,0.1); }
    form { display:flex; gap:8px; flex-wrap:wrap; }
    label { display:flex; flex-direction:column; font-size:13px; min-width:120px; }
    input { padding:6px 8px; border:1px solid #ccc; border-radius:4px; }
    button { padding:8px 12px; background:#2b6cb0; color:white; border:0; border-radius:6px; cursor:pointer; }
    table { width:100%; border-collapse:collapse; margin-top:12px; }
    th, td { padding:8px; border-bottom:1px solid #eee; text-align:left; font-size:14px; }
    th { background:#f1f7ff; }
  </style>
</head>
<body>

<?php
// Handle Add or Update
if (isset($_POST['save'])) {
  $name = $_POST['name'];
  $q1 = $_POST['q1'];
  $q2 = $_POST['q2'];
  $q3 = $_POST['q3'];
  $q4 = $_POST['q4'];

  if ($_POST['id'] == '') {
    $conn->query("INSERT INTO students (name,q1,q2,q3,q4) VALUES ('$name','$q1','$q2','$q3','$q4')");
  } else {
    $id = $_POST['id'];
    $conn->query("UPDATE students SET name='$name',q1='$q1',q2='$q2',q3='$q3',q4='$q4' WHERE id=$id");
  }
  header("Location: index.php");
}

// Handle Delete
if (isset($_GET['delete'])) {
  $id = $_GET['delete'];
  $conn->query("DELETE FROM students WHERE id=$id");
  header("Location: index.php");
}

// If editing
$editData = null;
if (isset($_GET['edit'])) {
  $id = $_GET['edit'];
  $res = $conn->query("SELECT * FROM students WHERE id=$id");
  $editData = $res->fetch_assoc();
}

// Fetch all students with average and ranking
$students = $conn->query("
  SELECT *, (q1+q2+q3+q4)/4 AS avg_grade
  FROM students
  ORDER BY avg_grade DESC
");

// Top performers per quarter using subqueries
function top_performer($conn, $q) {
  $res = $conn->query("SELECT name, $q FROM students WHERE $q = (SELECT MAX($q) FROM students) LIMIT 1");
  if ($res && $res->num_rows > 0) {
    $row = $res->fetch_assoc();
    return $row['name'].' ('.$row[$q].')';
  } else {
    return "—";
  }
}
?>

<h1>Performance Insight</h1>
<div class="insight-container">
  <div class="insight-box"><h3>Quarter 1</h3><p>Top Performer: <?= top_performer($conn, 'q1') ?></p></div>
  <div class="insight-box"><h3>Quarter 2</h3><p>Top Performer: <?= top_performer($conn, 'q2') ?></p></div>
  <div class="insight-box"><h3>Quarter 3</h3><p>Top Performer: <?= top_performer($conn, 'q3') ?></p></div>
  <div class="insight-box"><h3>Quarter 4</h3><p>Top Performer: <?= top_performer($conn, 'q4') ?></p></div>
</div>

<h1>Quarter Grades Activity (CRUD + SQL)</h1>
<section class="card">
  <form method="post">
    <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
    <label>Student Name <input name="name" type="text" required value="<?= $editData['name'] ?? '' ?>"></label>
    <label>Quarter 1 <input name="q1" type="number" min="0" max="100" required value="<?= $editData['q1'] ?? '' ?>"></label>
    <label>Quarter 2 <input name="q2" type="number" min="0" max="100" required value="<?= $editData['q2'] ?? '' ?>"></label>
    <label>Quarter 3 <input name="q3" type="number" min="0" max="100" required value="<?= $editData['q3'] ?? '' ?>"></label>
    <label>Quarter 4 <input name="q4" type="number" min="0" max="100" required value="<?= $editData['q4'] ?? '' ?>"></label>
    <button type="submit" name="save"><?= $editData ? 'Update' : 'Add' ?></button>
  </form>

  <table>
    <thead>
      <tr>
        <th>Rank</th>
        <th>Name</th>
        <th>Q1</th>
        <th>Q2</th>
        <th>Q3</th>
        <th>Q4</th>
        <th>Average</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php
      if ($students->num_rows == 0) {
        echo '<tr><td colspan="8" style="text-align:center;color:#666;">No records yet</td></tr>';
      } else {
        $rank = 1;
        while ($row = $students->fetch_assoc()) {
          echo "<tr>
            <td>{$rank}</td>
            <td>{$row['name']}</td>
            <td>{$row['q1']}</td>
            <td>{$row['q2']}</td>
            <td>{$row['q3']}</td>
            <td>{$row['q4']}</td>
            <td>".number_format($row['avg_grade'],2)."</td>
            <td>
              <a href='?edit={$row['id']}'>Edit</a> |
              <a href='?delete={$row['id']}' onclick=\"return confirm('Delete this record?')\">Delete</a>
            </td>
          </tr>";
          $rank++;
        }
      }
      ?>
    </tbody>
  </table>
</section>
</body>
</html>
