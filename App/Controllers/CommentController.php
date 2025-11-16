<?php

namespace App\Controllers;

use App\Models\CommentModel;
use App\Core\Response;

class CommentController
{
    private $commentModel;

    public function __construct()
    {
        $this->commentModel = new CommentModel();
    }

    /**
     * Listar comentarios de un libro.
     *
     * @param int $bookId ID del libro.
     * @return void
     */
    public function index(int $bookId)
    {
        $comments = $this->commentModel->getByBookId($bookId);
        Response::json(['success' => true, 'comments' => $comments]);
    }

    /**
     * Listar comentarios del usuario autenticado.
     *
     * @param int $userId ID del usuario autenticado.
     * @return void
     */
    public function getCommentsByUser(int $userId)
    {
        $comments = $this->commentModel->getByUser($userId);
        if (!$comments) {
            Response::json(['success' => false, 'message' => 'No se encontraron comentarios'], 404);
            return;
        }
        Response::json(['success' => true, 'comments' => $comments]);
    }

    /**
     * Agregar un comentario a un libro.
     *
     * @param int $userId ID del usuario autenticado (pasado por middleware).
     * @param int $bookId ID del libro.
     * @return void
     */
    public function store(int $userId, int $bookId)
    {
        $data = json_decode(file_get_contents("php://input"), true);
        $commentText = trim($data['comment'] ?? '');

        if (empty($commentText)) {
            Response::json(['success' => false, 'message' => 'El comentario no puede estar vacío'], 400);
            return;
        }

        $commentId = $this->commentModel->create([
            'user_id' => $userId,
            'book_id' => $bookId,
            'comment' => $commentText
        ]);

        if (!$commentId) {
            Response::json(['success' => false, 'message' => 'Error al agregar comentario'], 500);
            return;
        }

        Response::json(['success' => true, 'message' => 'Comentario agregado', 'commentId' => $commentId]);
    }

    /**
     * Eliminar un comentario.
     *
     * @param int $userId ID del usuario autenticado.
     * @param int $commentId ID del comentario a eliminar.
     * @return void
     */
    public function destroy(int $userId, int $commentId)
    {
        $comment = $this->commentModel->getById($commentId);

        if (!$comment) {
            Response::json(['success' => false, 'message' => 'Comentario no encontrado'], 404);
            return;
        }

        if ($comment['user_id'] !== $userId) {
            Response::json(['success' => false, 'message' => 'No autorizado'], 403);
            return;
        }

        $deleted = $this->commentModel->delete($commentId);
        if (!$deleted) {
            Response::json(['success' => false, 'message' => 'Error al eliminar comentario'], 500);
            return;
        }

        Response::json(['success' => true, 'message' => 'Comentario eliminado']);
    }
}
