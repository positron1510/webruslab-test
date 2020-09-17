<?php

namespace App\Console\Commands;

use App\Memcache;
use App\Repository;
use Illuminate\Console\Command;

class PostCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'views:save';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Консольная команда сброса кэша просмотров в базу';

    /**
     * @var Memcache
     *
     * Также как и в контроллере создаем экземпляр класса Memcache
     */
    private $memcache;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->memcache = new Memcache();
    }

    /**
     * @param Repository $repository
     *
     * Сохранение просмотров в базу с блокировкой ключа posts в кэше
     */
    public function handle(Repository $repository)
    {
        $this->memcache->lock();
        $count_posts = $repository->savePostsViews();
        $this->memcache->unlock();

        if ($count_posts) {
            $this->info('Просмотры добавлены в базу, ключ posts в кэше - пустой массив');
        }
    }
}
