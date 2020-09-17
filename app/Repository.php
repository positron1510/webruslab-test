<?php

namespace App;

use DB;
use App\Memcache;

/**
 * Class Repository
 * @package App
 *
 * Класс содержащий методы работы с базой
 */
class Repository
{
    /**
     * @return mixed
     *
     * Получение всех постов
     */
    public function getAllPosts():array
    {
        $posts = DB::select('SELECT id,views FROM post');
        $posts = array_map(function ($post){
            $post->views += Memcache::getViewsByPostId($post->id);
            return $post;
        }, $posts);

        return $posts;
    }

    /**
     * @param int $post_id
     * @return mixed
     *
     * Получение поста по идентификатору
     */
    public function getOnePost(int $post_id):\stdClass
    {
        $post = DB::select('SELECT id,views FROM post WHERE id=?', [$post_id]);
        if (!isset($post[0])) {
            return new \stdClass();
        }
        $post[0]->views += Memcache::getViewsByPostId($post[0]->id);

        return $post[0];
    }

    /**
     *
     * Добавление нового поста
     */
    public function addPost():void
    {
       DB::insert('INSERT INTO post (views) VALUES (0)');
       $post = DB::select('SELECT LAST_INSERT_ID() AS post_id');
       if (isset($post[0]->post_id)) {
           $this->insertOrUpdateViews($post[0]->post_id);
       }
    }

    /**
     * @param int $post_id
     * @param int $count
     *
     * Добавление записи в таблицу views по текущей дате либо
     * если post_id с сегодняшней датой есть, обновление счетчика просмотров по дате
     */
    public function insertOrUpdateViews(int $post_id, int $count=0):void
    {
        $views = DB::select('SELECT post_id,value FROM views WHERE post_id=? AND dt=DATE(NOW())', [$post_id]);
        if ($views) {
            if (isset($views[0]->value)) {
                $count += $views[0]->value;
                DB::update('UPDATE views SET value=? WHERE post_id=? AND dt=DATE(NOW())', [$count, $post_id]);
            }
        }else {
            DB::insert('INSERT INTO views (post_id, value, dt) VALUES (?, 0, DATE(NOW()))', [$post_id]);
        }
    }

    /**
     * @return int
     *
     * Сохранение просмотров по постам в базу и сброс кэша
     */
    public function savePostsViews():int
    {
        $posts = Memcache::get('posts');

        if ($posts) {
            foreach ($posts as $post_id=>$views) {
                $post_data = DB::select('SELECT views FROM post WHERE id=?', [$post_id]);
                if (isset($post_data[0])) {
                    $views += $post_data[0]->views;
                    DB::update('UPDATE post SET views=? WHERE id=?', [$views, $post_id]);
                    $this->insertOrUpdateViews($post_id, $post_data[0]->views);
                }
            }
            Memcache::forever('posts', []);
        }

        return count($posts);
    }
}