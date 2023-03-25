<?php

// Silly thing because PHP forbid string concatenation in class const
define('SQL_BOOKS_LEFT_JOIN', 'left outer join comments on comments.book = books.id
                                left outer join books_ratings_link on books_ratings_link.book = books.id
                                left outer join ratings on books_ratings_link.rating = ratings.id ');

const SELECT_BOOKS = 'select {0} from books ';
const SELECT_BOOKS_WITH = 'select {0} from {2}, books ';

define('SQL_BOOKS_ALL', SELECT_BOOKS . SQL_BOOKS_LEFT_JOIN . ' order by books.sort ');
define('SQL_BOOKS_BY_PUBLISHER', 'select {0} from books_publishers_link, books ' . SQL_BOOKS_LEFT_JOIN . ' where books_publishers_link.book = books.id and publisher = ? {1} order by publisher');
define('SQL_BOOKS_BY_FIRST_LETTER', SELECT_BOOKS . SQL_BOOKS_LEFT_JOIN . ' where upper (books.sort) like ? order by books.sort');
define('SQL_BOOKS_BY_AUTHOR', 'select {0} from books_authors_link, books ' . SQL_BOOKS_LEFT_JOIN . ' left outer join books_series_link on books_series_link.book = books.id  where books_authors_link.book = books.id and author = ? {1} order by series desc, series_index asc, pubdate asc');
define('SQL_BOOKS_BY_SERIE', 'select {0} from books_series_link, books ' . SQL_BOOKS_LEFT_JOIN . ' where books_series_link.book = books.id and series = ? {1} order by series_index');
define('SQL_BOOKS_BY_TAG', 'select {0} from books_tags_link, books ' . SQL_BOOKS_LEFT_JOIN . '  where books_tags_link.book = books.id and tag = ? {1} order by sort');
define('SQL_BOOKS_BY_LANGUAGE', 'select {0} from books_languages_link, books ' . SQL_BOOKS_LEFT_JOIN . ' where books_languages_link.book = books.id and lang_code = ? {1} order by sort');
define('SQL_BOOKS_BY_CUSTOM', SELECT_BOOKS_WITH . SQL_BOOKS_LEFT_JOIN . ' where {2}.book = books.id and {2}.{3} = ? {1} order by sort');
define('SQL_BOOKS_BY_CUSTOM_BOOL_TRUE', SELECT_BOOKS_WITH . SQL_BOOKS_LEFT_JOIN . ' where {2}.book = books.id and {2}.value = 1 {1} order by sort');
define('SQL_BOOKS_BY_CUSTOM_BOOL_FALSE', SELECT_BOOKS_WITH . SQL_BOOKS_LEFT_JOIN . ' where {2}.book = books.id and {2}.value = 0 {1} order by sort');
define('SQL_BOOKS_BY_CUSTOM_BOOL_NULL', SELECT_BOOKS . SQL_BOOKS_LEFT_JOIN . '  where books.id not in (select book from {2}) {1} order by sort');
define('SQL_BOOKS_BY_CUSTOM_RATING', SELECT_BOOKS . SQL_BOOKS_LEFT_JOIN . ' left join {2} on {2}.book = books.id left join {3} on {3}.id = {2}.{4} where {3}.value = ?  order by sort');
define('SQL_BOOKS_BY_CUSTOM_RATING_NULL', SELECT_BOOKS . SQL_BOOKS_LEFT_JOIN . ' left join {2} on {2}.book = books.id left join {3} on {3}.id = {2}.{4} where ((books.id not in (select {2}.book from {2})) or ({3}.value = 0)) {1} order by sort');
define('SQL_BOOKS_BY_CUSTOM_DATE', SELECT_BOOKS_WITH . SQL_BOOKS_LEFT_JOIN . ' where {2}.book = books.id and date({2}.value) = ? {1} order by sort');
define('SQL_BOOKS_BY_CUSTOM_DIRECT', SELECT_BOOKS_WITH . SQL_BOOKS_LEFT_JOIN . ' where {2}.book = books.id and {2}.value = ? {1} order by sort');
define('SQL_BOOKS_BY_CUSTOM_DIRECT_ID', SELECT_BOOKS_WITH . SQL_BOOKS_LEFT_JOIN . '  where {2}.book = books.id and {2}.id = ? {1} order by sort');
define('SQL_BOOKS_QUERY', SELECT_BOOKS . SQL_BOOKS_LEFT_JOIN . ' where ( exists (select null from authors, books_authors_link where book = books.id and author = authors.id and authors.name like ?) or exists (select null from tags, books_tags_link where book = books.id and tag = tags.id and tags.name like ?) or exists (select null from series, books_series_link on book = books.id and books_series_link.series = series.id and series.name like ?) or exists (select null from publishers, books_publishers_link where book = books.id and books_publishers_link.publisher = publishers.id and publishers.name like ?) or title like ?) {1} order by books.sort');
define('SQL_BOOKS_RECENT', SELECT_BOOKS . SQL_BOOKS_LEFT_JOIN . ' where 1=1 {1} order by timestamp desc limit ');
define('SQL_BOOKS_BY_RATING', SELECT_BOOKS . SQL_BOOKS_LEFT_JOIN . ' where books_ratings_link.book = books.id and ratings.id = ? {1} order by sort');
