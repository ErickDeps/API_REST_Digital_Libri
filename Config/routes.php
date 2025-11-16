<?php

use App\Controllers\AuthController;
use App\Controllers\UserController;
use App\Controllers\BookController;
use App\Controllers\GenreController;
use App\Controllers\BookGenreController;
use App\Controllers\CommentController;
use App\Controllers\RatingController;
use App\Controllers\FavoriteController;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use App\Middleware\SoftAuthMiddleware;

return [
    // Autenticación
    ['route' => '/login', 'controller' => AuthController::class, 'action' => 'login', 'method' => 'POST'],
    ['route' => '/logout', 'controller' => AuthController::class, 'action' => 'logout', 'method' => 'POST'],
    ['route' => '/register', 'controller' => AuthController::class, 'action' => 'register', 'method' => 'POST'],

    // Perfil
    ['route' => '/profile', 'controller' => UserController::class, 'action' => 'profile', 'method' => 'GET', 'middleware' => AuthMiddleware::class],
    ['route' => '/user-context', 'controller' => UserController::class, 'action' => 'userContext', 'method' => 'GET', 'middleware' => SoftAuthMiddleware::class],

    // Libros (RESTful)
    ['route' => '/books', 'controller' => BookController::class, 'action' => 'index', 'method' => 'GET', 'middleware' => SoftAuthMiddleware::class],
    ['route' => '/books', 'controller' => BookController::class, 'action' => 'store', 'method' => 'POST', 'middleware' => [AuthMiddleware::class, RoleMiddleware::class], 'role' => 'author'],
    ['route' => '/books/{id}', 'controller' => BookController::class, 'action' => 'show', 'method' => 'GET'],
    ['route' => '/books/{id}', 'controller' => BookController::class, 'action' => 'update', 'method' => 'PUT', 'middleware' => [AuthMiddleware::class, RoleMiddleware::class], 'role' => 'author'],
    ['route' => '/books/{id}', 'controller' => BookController::class, 'action' => 'destroy', 'method' => 'DELETE', 'middleware' => [AuthMiddleware::class, RoleMiddleware::class], 'role' => 'author'],

    // Libros por usuario
    ['route' => '/user/books', 'controller' => BookController::class, 'action' => 'getByUser', 'method' => 'GET', 'middleware' => AuthMiddleware::class],

    // Favoritos
    ['route' => '/books/{id}/favorite', 'controller' => FavoriteController::class, 'action' => 'toggle', 'method' => 'POST', 'middleware' => AuthMiddleware::class],
    ['route' => '/favorites', 'controller' => FavoriteController::class, 'action' => 'index', 'method' => 'GET', 'middleware' => AuthMiddleware::class],

    // Géneros
    ['route' => '/genres', 'controller' => GenreController::class, 'action' => 'index', 'method' => 'GET'],
    ['route' => '/genres', 'controller' => GenreController::class, 'action' => 'store', 'method' => 'POST', 'middleware' => [AuthMiddleware::class, RoleMiddleware::class], 'role' => 'author'],
    ['route' => '/genres/{id}', 'controller' => GenreController::class, 'action' => 'update', 'method' => 'PUT', 'middleware' => [AuthMiddleware::class, RoleMiddleware::class], 'role' => 'author'],
    ['route' => '/genres/{id}', 'controller' => GenreController::class, 'action' => 'destroy', 'method' => 'DELETE', 'middleware' => [AuthMiddleware::class, RoleMiddleware::class], 'role' => 'author'],

    // Géneros de un libro
    ['route' => '/books/{id}/genres', 'controller' => BookGenreController::class, 'action' => 'index', 'method' => 'GET'],
    ['route' => '/books/{id}/genres', 'controller' => BookGenreController::class, 'action' => 'store', 'method' => 'POST', 'middleware' => AuthMiddleware::class],
    ['route' => '/books/{bookId}/genres/{genreId}', 'controller' => BookGenreController::class, 'action' => 'destroy', 'method' => 'DELETE', 'middleware' => [AuthMiddleware::class, RoleMiddleware::class], 'role' => 'author'],

    // Comentarios
    ['route' => '/books/{id}/comments', 'controller' => CommentController::class, 'action' => 'index', 'method' => 'GET'],
    ['route' => '/books/{id}/comments', 'controller' => CommentController::class, 'action' => 'store', 'method' => 'POST', 'middleware' => AuthMiddleware::class],
    ['route' => '/comments/{id}', 'controller' => CommentController::class, 'action' => 'destroy', 'method' => 'DELETE', 'middleware' => AuthMiddleware::class],

    // Comentarios por usuario
    ['route' => '/user/comments', 'controller' => CommentController::class, 'action' => 'getCommentsByUser', 'method' => 'GET', 'middleware' => AuthMiddleware::class],

    // Ratings
    ['route' => '/books/{id}/ratings', 'controller' => RatingController::class, 'action' => 'index', 'method' => 'GET'],
    ['route' => '/books/{id}/ratings', 'controller' => RatingController::class, 'action' => 'store', 'method' => 'POST', 'middleware' => AuthMiddleware::class],
];
