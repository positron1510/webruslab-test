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
    const POST_CACHE_TIME = 2;

    /**
     *
     * Задержка между итерациями цикла при блокировке
     */
    const DELAY = 1000;

    /**
     * @param int $post_id
     *
     * Увеличение количества просмотров поста на 1
     * и сохранение обновленного массива $posts в кэше
     */
    public function incrementPostViews(int $post_id):void
    {
        # блокировка по ключу lock<post_id> в мемкэше
        $this->lock($post_id);

        if (!self::has($post_id)) {
            self::put($post_id, 0, self::POST_CACHE_TIME);
        }
        # тот самый атомарный инкремент
        self::increment($post_id);

        # разблокировка ключа lock<post_id> в мемкэше
        $this->unlock($post_id);
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
     * @param int $post_id
     *
     * Блокировка
     */
    public function lock(int $post_id=0):void
    {
        while (!self::add(sprintf('lock%s', $post_id), '', self::CACHE_TIME)) {
            usleep(self::DELAY);
        }
    }

    /**
     * @param int $post_id
     *
     * Разблокировка
     */
    public function unlock(int $post_id=0):void
    {
        self::forget(sprintf('lock%s', $post_id));
    }
}