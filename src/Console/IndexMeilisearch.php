<?php

namespace Meilisearch\Scout\Console;

use Illuminate\Console\Command;
use MeiliSearch\Client;
use MeiliSearch\Exceptions\HTTPRequestException;
use Meilisearch\Scout\Events\IndexCreated;

class IndexMeilisearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:index {--d|delete : Delete an existing index} {--k|key= : The name of primary key} {name : The name of the index}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or delete an index';

    /**
     * Execute the console command.
     *
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     *
     * @return void
     */
    public function handle()
    {
        $client = new Client(config('meilisearch.host'), config('meilisearch.key'));

        try {
            if ($this->option('delete')) {
                $client->deleteIndex($this->argument('name'));
                $this->info('Index "'.$this->argument('name').'" deleted.');

                return;
            }

            $creation_options = [];
            if ($this->option('key')) {
                $creation_options = ['primaryKey' => $this->option('key')];
            }
            $index = $client->createIndex(
                $this->argument('name'),
                $creation_options
            );

            IndexCreated::dispatch($index);

            $this->info('Index "'.$this->argument('name').'" created.');
        } catch (HTTPRequestException $exception) {
            $this->error($exception->getMessage());
        }
    }
}
