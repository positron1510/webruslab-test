<?php

namespace App\Http\Controllers;

use App\Memcache;
use App\Repository;

/**
 * Class PostController
 * @package App\Http\Controllers
 *
 * Основной контроллер для работы с постами и кэшем
 */
class PostController extends Controller
{
    /**
     * @var Repository
     *
     * Экземпляр класса-репозитория для работы с базой
     */
    private $repository;

    /**
     * PostController constructor.
     *
     * Инициализируем экземпляр класса-репозитория для работы с базой
     */
    public function __construct()
    {
        $this->repository = new Repository();
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @method GET
     * @route /posts
     *
     * Вывод всех постов
     */
    public function getPosts()
    {
        return view('posts', [
            'posts' => $this->repository->getAllPosts()
        ]);
    }

    /**
     * @param Memcache $memcache
     * @param int $post_id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @method GET
     * @route /post/{post_id}
     *
     * Вывод поста по его идентификатору
     */
    public function getOnePost(Memcache $memcache, int $post_id)
    {
        $memcache->incrementPostViews($post_id);

        return view('one_post', [
            'post' => $this->repository->getOnePost($post_id)
        ]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     *
     * @method GET
     * @route /post/add
     *
     * Добавление поста в базу
     */
    public function addPost()
    {
        $this->repository->addPost();
        return redirect()->route('posts');
    }
}
