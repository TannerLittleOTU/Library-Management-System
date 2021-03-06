<?php 

$where = new WhereClause("and");

if (isset($_GET['search'])) {
    $where->add("title LIKE %ss", $_GET['search']);
}

if (isset($_GET['genre'])) {
    $genre = DB::queryFirstField("SELECT name FROM genres WHERE id = %i", $_GET['genre']);
    if ($genre) $where->add("id IN (SELECT book_id FROM book_genres WHERE genre_id = %i)", $_GET['genre']);
}

if (isset($_GET['publisher'])) {
    $publisher = DB::queryFirstField("SELECT name FROM publishers WHERE id = %i", $_GET['publisher']);
    if ($publisher) $where->add("publisher_id = %i", $_GET['publisher']);
}

if (isset($_GET['author'])) {
    $author = DB::queryFirstField("SELECT CONCAT(name_first, IFNULL(CONCAT(' ', name_middle), ''), ' ', name_last) AS name FROM authors WHERE id = %i", $_GET['author']);
    if ($author) $where->add("id IN (SELECT book_id FROM book_authors WHERE author_id = %i)", $_GET['author']);
}

$count = DB::queryFirstField("SELECT COUNT(*) FROM books WHERE %l", $where);

$pagination = pagination($count, 5);
$order_by = $pagination['order_by'];
$sort_by = $pagination['sort_by'];
$limit = $pagination['limit'];
$offset = $pagination['offset'];

$books = DB::query("SELECT id, title, pages, rating, isbn, published_at FROM books WHERE %l ORDER BY %b $sort_by LIMIT %i OFFSET %i", $where, $order_by, $limit, $offset);

include('includes/header.php');

?>

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-book"></i> Books</h1>
        <!--<div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-sm btn-outline-secondary">Share</button>
                <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle">
            <span data-feather="calendar"></span>
            This week
            </button>
        </div>-->
    </div>
    <div class="row">
        <div class="col-sm-8"><h2><?= (empty($genre) ? "All" : $genre) ?> <strong>Books<?= (empty($author) ? "" : "</strong> by <strong>" . $author) ?></strong><?= (empty($publisher) ? "" : '<br><p class="lead">Published by <strong>' . $publisher . '</strong></p>') ?><?= (empty($_GET['search']) ? '' : '<p class="lead">Showing ' . $count . ' results for <strong>' . sanitize($_GET['search']) . '</strong>.</p>') ?></h2></div>
        <div class="col-sm-4">
            <form class="form-group search" method="GET" action="">
                <div class="form-group search">
                    <span class="fa fa-search form-control-feedback"></span>
                    <input type="text" name="search" class="form-control" placeholder="Search" value="<?= get_value('search', '') ?>">
                    <input type="submit" hidden>
                </div>
            </form>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col" width="5%">#</th>
                    <th scope="col" width="64%"><a href="<?= modify_query_url([ 'order_by' => 'title', 'sort_by' => ($sort_by == "ASC" ? 'desc' : 'asc') ]) ?>" class="text-decoration-none text-dark">Title<?= (($order_by == "title") ? ' <i class="fas fa-caret-' . (($sort_by == "ASC") ? 'up' : 'down') . '"></i>' : '') ?></a></th>
                    <th scope="col" width="8%"><a href="<?= modify_query_url([ 'order_by' => 'pages', 'sort_by' => ($sort_by == "ASC" ? 'desc' : 'asc') ]) ?>" class="text-decoration-none text-dark">Pages<?= (($order_by == "pages") ? ' <i class="fas fa-caret-' . (($sort_by == "ASC") ? 'up' : 'down') . '"></i>' : '') ?></a></th>
                    <th scope="col" width="8%"><a href="<?= modify_query_url([ 'order_by' => 'rating', 'sort_by' => ($sort_by == "ASC" ? 'desc' : 'asc') ]) ?>" class="text-decoration-none text-dark">Rating<?= (($order_by == "rating") ? ' <i class="fas fa-caret-' . (($sort_by == "ASC") ? 'up' : 'down') . '"></i>' : '') ?></a></th>
                    <th scope="col" width="15%"><a href="<?= modify_query_url([ 'order_by' => 'published_at', 'sort_by' => ($sort_by == "ASC" ? 'desc' : 'asc') ]) ?>" class="text-decoration-none text-dark">Publish Date<?= (($order_by == "published_at") ? ' <i class="fas fa-caret-' . (($sort_by == "ASC") ? 'up' : 'down') . '"></i>' : '') ?></a></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($books as $book): ?>
                    <tr class="cursor-pointer" onclick="window.location.href='/book/<?= strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $book['title']))); ?>-<?= $book['id'] ?>'">
                        <td><?= $book['id'] ?></td>
                        <td><?= $book['title'] ?></td>
                        <td><?= $book['pages'] ?></td>
                        <td><?= $book['rating'] ?></td>
                        <td><?= date('M j, Y', strtotime($book['published_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="row">
        <div class="col-4 hint-text">Showing <b><?= $pagination['start'] ?> - <?= $pagination['end'] ?></b> out of <b><?= $pagination['count'] ?></b> entries</div>
        <nav class="col-8">
            <ul class="pagination justify-content-end">
                <li class="page-item<?= ($pagination['page'] == 1) ? ' disabled' : '' ?>">
                    <a class="page-link" href="<?= modify_query_url('offset', 0) ?>" aria-label="First">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>

                <?php foreach ($pagination['buttons'] as $button): ?>
                    <li class="page-item<?= ($button['active'] ? ' active' : '') ?>"><a class="page-link" href="<?= modify_query_url('offset', ($button['number'] - 1) * $pagination['limit']) ?>"><?= $button['number'] ?></a></li>
                <?php endforeach; ?>

                <li class="page-item<?= ($pagination['page'] == $pagination['pages']) ? ' disabled' : '' ?>">
                    <a class="page-link" href="<?= modify_query_url('offset', ($pagination['pages'] - 1) * $pagination['limit'] ) ?>" aria-label="Last">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

<?php include('includes/footer.php'); ?>