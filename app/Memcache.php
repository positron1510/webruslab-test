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
     * Время жизни ключа post_id в кэше
     */
    const POST_CACHE_TIME = 1;

    /**
     *
     * Задержка между итерациями цикла при блокировке
     */
    const DELAY = 1000;

    /**
     * Memcache constructor.
     *
     * В массив posts складываем идентификаторы постов
     * на которые кликали в течении минуты
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
     */
    public function incrementPostViews(int $post_id):void
    {
        $result = self::increment($post_id);

        if (!$result) {
            $this->lock();

            self::add($post_id, 1, self::POST_CACHE_TIME);

            $posts = self::get('posts');
            $posts[] = $post_id;
            self::forever('posts', $posts);

            $this->unlock();
        }
    }

    /**
     * @param int $post_id
     * @return int
     *
     * Кол-во просмотров поста в кэше на данный момент
     */
    public static function getViewsByPostId(int $post_id):int
    {
        return (int) self::get($post_id);
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