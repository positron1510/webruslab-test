<?php

namespace App;

use Illuminate\Support\Facades\Cache;

/**
 * Class Memcache
 * @package App
 *
 * Создаем свой кастомный класс кэширования отнаследовавшись
 * от стандартного ларавелевского класса Cache
 */
class Memcache extends Cache
{
    /**
     *
     * Время жизни ключа блокировки в кэше в минутах
     */
    const CACHE_TIME = 1;

    /**
     *
     * Задержка между итерациями цикла при блокировке
     */
    const DELAY = 1000;

    /**
     * Memcache constructor.
     *
     * При вызове любого метода контроллера проверяем есть ли в кэше ключ posts
     * если нет создаем присваивая значение пустой массив
     * в нем будут храниться пары $post_id => $count_views
     */
    public function __construct()
    {
        if (!self::has('posts')) {
            self::forever('posts', []);
        }
    }

    /**
     * @param int $post_id
     *
     * Увеличение количества просмотров поста на 1
     * и сохранение обновленного массива $posts в кэше
     */
    public function incrementPostViews(int $post_id):void
    {
        $this->lock();

        $posts = self::get('posts');
        $posts[$post_id] = isset($posts[$post_id]) ? $posts[$post_id] += 1 : 1;
        self::forever('posts', $posts);

        $this->unlock();
    }

    /**
     * @param int $post_id
     * @return int
     *
     * Кол-во просмотров поста в кэше на данный момент
     */
    public static function getViewsByPostId(int $post_id):int
    {
        $posts = self::get('posts');
        return $posts[$post_id] ?? 0;
    }

    /**
     *
     * Блокировка
     */
    public function lock():void
    {
        while (!self::add('lock', '', self::CACHE_TIME)) {
            usleep(self::DELAY);
        }
    }

    /**
     *
     * Разблокировка
     */
    public function unlock():void
    {
        self::forget('lock');
    }
}