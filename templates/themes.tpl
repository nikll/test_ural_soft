<? include('header.tpl') ?>
<?  include('menu.tpl')  ?>

<div class="container theme-showcase" role="main">

    <style type="text/css" scoped="">
        .forum_themes th {
            white-space: nowrap;
        }
        .forum_themes td {
            white-space: nowrap;
            cursor: pointer;
        }
    </style>


    <div class="page-header">
        <h1>Список тем</h1>
    </div>

    <div class="row">
        <form method="post">
            <input type="hidden" name="action" value="add_theme">

            <table class="table table-bordered forum_themes">
                <thead>
                <tr>
                    <th style="width:100%">Тема</th>
                    <th>Автор</th>
                    <th>Дата последнего обновления</th>
                    <th>Количество сообщений</th>
                </tr>
                </thead>
                <tbody>
            <?foreach ($themes as $id => $theme):?>
                <tr>
                    <td><a href="?theme=<?=$id?>"><?=$theme['name']?></a></td>
                    <td><?=$authors[$theme['author']]?></td>
                    <td><?=($theme['cnt'] ? $theme['last_date'].'('.$authors[$theme['last_author']].')' : '')?></td>
                    <td><?=$theme['cnt']?></td>
                </tr>
            <?endforeach?>
                <tr>
                    <td><input type="text" name="name"   required class="form-control" placeholder="Новая тема" aria-describedby="basic-addon1"></td>
                    <td><input type="text" name="author" required class="form-control" placeholder="Автор" aria-describedby="basic-addon1" value="<?=$_SESSION['author']?>"></td>
                    <td colspan="2"><button type="submit" class="btn btn-default">Создать</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>

</div> <!-- /container -->

<? include('footer.tpl') ?>
