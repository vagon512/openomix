<form method="post">

    <select name="data" >
        <option value = 1>a</option>
        <option value = 2>B</option>
        <option value = 3>C</option>
        <option value = 4>D</option>
    </select>

    <input type="checkbox" name="data2[]" value="1">1<br>
    <input type="checkbox" name="data2[]" value="2">2<br>
    <input type="checkbox" name="data2[]" value="3">3<br>
    <input type="submit" value="add">
</form>

<?php
print_r($_POST);
?>