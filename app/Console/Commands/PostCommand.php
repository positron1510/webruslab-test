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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param Repository $repository
     * @param Memcache $memcache
     *
     * Сохранение просмотров в базу с блокировкой ключа posts в кэше
     */
    public function handle(Repository $repository, Memcache $memcache)
    {
        $count_posts = $repository->savePostsViews($memcache);

        if ($count_posts) {
            $this->info('Просмотры добавлены в базу');
        }
    }
}
