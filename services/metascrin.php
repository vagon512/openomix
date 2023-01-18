<!--
<div class="form-width-outer">
    <div class="form-width-inner">
        <form  method="post" enctype="multipart/form-data">
            <input type="text" name="patientLastName" placeholder="фамилия">
            <input type="text" name="patientFirstName" placeholder="Имя">
            <input type="text" name="patientMidleName" placeholder="номер заявки">
            дата рождения<input type="date" name="birthday">
            <input type="text" name="researchNum" placeholder="Отчество">
            <input type="file" name="myFile">
            <button type="submit">Загрузить</button>
        </form>
    </div>
</div> -->
<div class="form-width-outer">
    <div class="form-width-inner">
        <form  method="post" action="cabinet.php?service=metascrin#tab2">

            <input type="text" name="RequestNr" placeholder="номер заявки">
            дата рождения<input type="date" name="BirthDate">

            <button type="submit">Загрузить</button>
        </form>
    </div>
</div>






