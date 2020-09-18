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
        $post = DB::table('post')
            ->select('id', 'views')
            ->where('id', $post_id)
            ->first();

        if (!$post) {
            return new \stdClass();
        }
        $post->views += Memcache::getViewsByPostId($post->id);

        return $post;
    }

    /**
     *
     * Добавление нового поста
     */
    public function addPost():void
    {
        $post_id = DB::table('post')
            ->insertGetId(['views' => 0]);

        if ($post_id) {
            $this->insertOrUpdateViews($post_id);
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
        $current_date = date('Y-m-d');

        $value = DB::table('views')
            ->where('post_id', $post_id)
            ->where('dt', $current_date)
            ->pluck('value')
            ->first();

        if (!is_null($value)) {
            $count += $value;
            DB::table('views')
                ->where('post_id', $post_id)
                ->where('dt', $current_date)
                ->update(['value' => $count]);
        }else {
            DB::table('views')
                ->insert(['post_id' => $post_id, 'value' => $count, 'dt' => $current_date]);
        }
    }

    /**
     * @param \App\Memcache $memcache
     * @return int
     *
     * Сохранение просмотров по постам в базу и сброс кэша
     * для получения всех ключей находящихся с мемкэше воспользуемся методом getAllKeys()
     * документация https://www.php.net/manual/ru/memcached.getallkeys.php
     * тут написано что мемкэш не гарантирует возврата всех ключей, тогда альтернатива пробежаться по всем айдишникам из базы?
     * или может быть воспользоваться другой системой кэширования?
     */
    public function savePostsViews(Memcache $memcache):int
    {
        $posts = Memcache::getStore()->getMemcached()->getAllKeys();

        $saved_views = 0;
        foreach ($posts as $post_id) {
            # ларавель почему то при вызове getAllKeys() к каждому ключу добавляет префикс laravel_cache:
            $post_id = (int) str_replace('laravel_cache:', '', $post_id);

            if (!$post_id) continue;

            $views = (int) DB::table('post')
                ->where('id', $post_id)
                ->pluck('views')
                ->first();

            # блокировка на изменение просмотров именно этого поста с $post_id
            $memcache->lock($post_id);

            $views_from_cache = Memcache::getViewsByPostId($post_id);
            $views += $views_from_cache;

            DB::table('post')
                ->where('id', $post_id)
                ->update(['views' => $views]);

            $this->insertOrUpdateViews($post_id, $views_from_cache);

            # удаляем кол-во просмотров поста, после чего снимаем блокировку
            Memcache::forget($post_id);

            $memcache->unlock($post_id);
            $saved_views++;
        }

        return $saved_views;
    }
}