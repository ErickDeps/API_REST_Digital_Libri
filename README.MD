# API de Biblioteca Digital

## Descripción general

Esta API RESTful permite gestionar una biblioteca digital con funcionalidades avanzadas de usuarios, libros, géneros, comentarios, calificaciones y favoritos.  
Está desarrollada en **PHP** con una arquitectura modular que facilita la escalabilidad, la seguridad y el mantenimiento del código.

El sistema implementa autenticación basada en **JSON Web Tokens (JWT)** con almacenamiento seguro en cookies, lo que permite proteger los endpoints sensibles y asignar permisos según el rol del usuario.  
Los roles disponibles son **autor** y **lector**, con permisos diferenciados para las operaciones de lectura y escritura.

Además, la API incluye una **búsqueda avanzada** que permite filtrar libros por título, autor, género o rango de años de publicación.

---

## Tecnologías utilizadas

- PHP 8+
- MySQL
- Composer
- Firebase PHP-JWT
- Arquitectura MVC
- Middlewares personalizados (Auth y Role)

---

## Autenticación y roles

El sistema utiliza autenticación JWT con cookies seguras (HttpOnly).  
Cada usuario obtiene un token válido durante un periodo determinado, el cual se verifica automáticamente en cada solicitud protegida mediante los middlewares de autenticación y roles.

**Roles disponibles:**

- **Lector:** puede visualizar libros, géneros, comentarios y agregar calificaciones o favoritos.
- **Autor:** además de lo anterior, puede crear, actualizar y eliminar libros y géneros.

---

## Endpoints principales

### 1. Autenticación

- **POST /api/register**  
  Registra un nuevo usuario con rol definido (autor o lector).

- **POST /api/login**  
  Inicia sesión y genera un token JWT almacenado en una cookie segura.

- **POST /api/logout**  
  Cierra la sesión del usuario eliminando el token.

- **GET /api/profile**  
  Retorna la información del usuario autenticado.

---

### 2. Libros

- **GET /api/books**  
  Lista todos los libros (acceso público).

- **POST /api/books**  
  Crea un nuevo libro (requiere autenticación y rol de autor).

- **PUT /api/books/{id}**  
  Actualiza un libro existente (requiere autenticación y rol de autor).

- **DELETE /api/books/{id}**  
  Elimina un libro (requiere autenticación y rol de autor).

---

### 3. Géneros

- **GET /api/genres**  
  Lista todos los géneros disponibles.

- **POST /api/genres**  
  Agrega un nuevo género (requiere autenticación y rol de autor).

- **PUT /api/genres/{id}**  
  Edita un género existente (requiere autenticación y rol de autor).

- **DELETE /api/genres/{id}**  
  Elimina un género (requiere autenticación y rol de autor).

- **POST /api/books/{book_id}/genres/{genre_id}**  
  Asigna un género a un libro (requiere autenticación y rol de autor).

- **DELETE /api/books/{book_id}/genres/{genre_id}**  
  Elimina la relación entre un libro y un género (requiere autenticación y rol de autor).

---

### 4. Comentarios

- **GET /api/books/{id}/comments**  
  Muestra los comentarios de un libro (público).

- **POST /api/books/{id}/comments**  
  Agrega un comentario (requiere autenticación).

- **DELETE /api/comments/{id}**  
  Elimina un comentario (requiere autenticación).

---

### 5. Calificaciones

- **POST /api/books/{id}/rating**  
  Agrega una calificación a un libro (requiere autenticación).

- **PUT /api/books/{id}/rating**  
  Actualiza la calificación de un libro (requiere autenticación).

---

### 6. Favoritos

- **POST /api/books/{id}/favorite**  
  Agrega un libro a favoritos (requiere autenticación).

- **DELETE /api/books/{id}/favorite**  
  Quita un libro de favoritos (requiere autenticación).

- **GET /api/favorites**  
  Lista todos los libros marcados como favoritos (requiere autenticación).

---

### 7. Búsqueda avanzada

- **GET /api/books/search**  
  Permite realizar una búsqueda personalizada mediante varios filtros opcionales:

  **Parámetros disponibles:**

  - `title`: búsqueda parcial por título.
  - `author`: nombre del autor.
  - `genres`: uno o varios géneros separados por comas.
  - `yearFrom`: año mínimo de publicación.
  - `yearTo`: año máximo de publicación.

**Ejemplo de solicitud:**
GET /api/books/search?title=Harry&&genres=1,5&yearFrom=2010&yearTo=2024

## Estructura del proyecto

```
Raíz del proyecto/
├── App/
│ ├── Controllers/
│ │ ├── AuthController.php
│ │ ├── BookController.php
│ │ ├── BookGenreController.php
│ │ ├── CommentController.php
│ │ ├── FavoriteController.php
│ │ ├── GenreController.php
│ │ ├── RatingController.php
│ │ └── UserController.php
│ ├── Core/
│ │ ├── Database.php
│ │ ├── JWTHandler.php
│ │ ├── Response.php
│ │ └── Router.php
│ ├── Middleware/
│ │ ├── AuthMiddleware.php
│ │ └── RoleMiddleware.php
│ └── Models/
│ ├── BookGenreModel.php
│ ├── BookModel.php
│ ├── CommentModel.php
│ ├── FavoriteModel.php
│ ├── GenreModel.php
│ ├── RatingModel.php
│ └── UserModel.php
├── Config/
│ ├── database.php
│ └── routes.php
├── Logs/
│ └── app.log
├── Public/
│ ├── index.php
│ └── .htaccess
├── Tests/
│ └── UserModelTest.php
├── Vendor/
├── composer.json
├── composer.lock
├── .env
└── .htaccess
```

---

## Ejemplo de respuesta JSON

**Éxito**

```json
{
  "success": true,
  "message": "Libro agregado correctamente",
  "data": {
    "id": 12,
    "title": "El viaje del programador",
    "author": "Erick Manzanarez",
    "year": 2024
  }
}
```

**Error**

```json
{
  "success": false,
  "message": "No tienes permisos para esta acción"
}
```

---

## Autor del proyecto

Erick Alberto Manzanarez Cortés
Desarrollador Web Backend
Acapulco, Guerrero, México
Correo: erickdepss@gmail.com
