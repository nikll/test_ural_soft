<? include('header.tpl') ?>
<? include('menu.tpl') ?>

<div class="container theme-showcase" role="main">

<style type="text/css" scoped="">
    .forum_themes td, .forum_themes th {
        white-space: nowrap;
    }
</style>


<div class="row">
    <?foreach ($messages as $message):?>
        <?var_dump($message)?>
    <div class="panel panel-default">
        <div class="panel-heading">
            Date: <?=$message['date']?>
            <div style="float: right">Автор: <?=$message['author']?> (зарегистрирован: <?=$message['author_reg']?>, сообщений: <?=$message['author_cnt_messages']?>)</div>
        </div>
        <div class="panel-body">
            <?=$message['text']?>
        </div>
    </div>
    <?endforeach?>

    <form method="post" action="<?=$_SERVER['REQUEST_URI']?>">
        <input type="hidden" name="action" value="add_message">

        <label for="author">Автор</label>
            <input type="text" name="author" required class="form-control" placeholder="Автор" aria-describedby="basic-addon1" value="<?=$_SESSION['author']?>">
        <label for="author">Сообщение</label>
            <textarea name="text" class="form-control" rows="10" placeholder="Сообщение" required></textarea>

        <button type="submit" class="btn btn-default">Создать</button>
    </form>
</div>

</div> <!-- /container -->

<? include('footer.tpl') ?>
