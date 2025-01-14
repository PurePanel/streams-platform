<?php namespace Anomaly\Streams\Platform\Stream\Console;

use Anomaly\Streams\Platform\Addon\Addon;
use Anomaly\Streams\Platform\Addon\AddonCollection;
use Anomaly\Streams\Platform\Stream\Console\Command\AppendEntityBindings;
use Anomaly\Streams\Platform\Stream\Console\Command\AppendEntityButtonLang;
use Anomaly\Streams\Platform\Stream\Console\Command\AppendEntityPermissionLang;
use Anomaly\Streams\Platform\Stream\Console\Command\AppendEntityPermissions;
use Anomaly\Streams\Platform\Stream\Console\Command\AppendEntityRoutes;
use Anomaly\Streams\Platform\Stream\Console\Command\AppendEntitySection;
use Anomaly\Streams\Platform\Stream\Console\Command\AppendEntitySectionLang;
use Anomaly\Streams\Platform\Stream\Console\Command\AppendEntitySingletons;
use Anomaly\Streams\Platform\Stream\Console\Command\AppendEntityStreamLang;
use Anomaly\Streams\Platform\Stream\Console\Command\WriteEntityCollection;
use Anomaly\Streams\Platform\Stream\Console\Command\WriteEntityController;
use Anomaly\Streams\Platform\Stream\Console\Command\WriteEntityCriteria;
use Anomaly\Streams\Platform\Stream\Console\Command\WriteEntityFactory;
use Anomaly\Streams\Platform\Stream\Console\Command\WriteEntityFormBuilder;
use Anomaly\Streams\Platform\Stream\Console\Command\WriteEntityModel;
use Anomaly\Streams\Platform\Stream\Console\Command\WriteEntityModelInterface;
use Anomaly\Streams\Platform\Stream\Console\Command\WriteEntityObserver;
use Anomaly\Streams\Platform\Stream\Console\Command\WriteEntityPresenter;
use Anomaly\Streams\Platform\Stream\Console\Command\WriteEntityRepository;
use Anomaly\Streams\Platform\Stream\Console\Command\WriteEntityRepositoryInterface;
use Anomaly\Streams\Platform\Stream\Console\Command\WriteEntityRouter;
use Anomaly\Streams\Platform\Stream\Console\Command\WriteEntitySeeder;
use Anomaly\Streams\Platform\Stream\Console\Command\WriteEntityTableBuilder;
use Anomaly\Streams\Platform\Stream\Console\Command\WriteEntityTestCases;
use Anomaly\Streams\Platform\Stream\Console\Command\WriteEntityTreeBuilder;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Class Make
 *
 * @link   http://pyrocms.com/
 * @author PyroCMS, Inc. <support@pyrocms.com>
 * @author Ryan Thompson <ryan@pyrocms.com>
 */
class Make extends Command
{

    use DispatchesJobs;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:stream';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make a streams entity namespace.';

    /**
     * Execute the console command.
     */
    public function handle(AddonCollection $addons)
    {
        $slug   = $this->argument('slug');
        $nested = $this->option('nested');

        /* @var Addon $addon */
        if (!$addon = $addons->get($this->argument('addon'))) {
            throw new \Exception("The addon [{$this->argument('addon')}] could not be found.");
        }

        if (!$namespace = $this->option('namespace')) {
            $namespace = $addon->getSlug();
        }

        dispatch_sync(new WriteEntityModel($addon, $slug, $namespace));
        dispatch_sync(new WriteEntityRouter($addon, $slug, $namespace));
        dispatch_sync(new WriteEntitySeeder($addon, $slug, $namespace));
        dispatch_sync(new WriteEntityFactory($addon, $slug, $namespace));
        dispatch_sync(new WriteEntityObserver($addon, $slug, $namespace));
        dispatch_sync(new WriteEntityCriteria($addon, $slug, $namespace));
        dispatch_sync(new WriteEntityPresenter($addon, $slug, $namespace));
        dispatch_sync(new WriteEntityCollection($addon, $slug, $namespace));
        dispatch_sync(new WriteEntityRepository($addon, $slug, $namespace));
        dispatch_sync(new WriteEntityFormBuilder($addon, $slug, $namespace));
        dispatch_sync(new WriteEntityController($addon, $slug, $namespace, $nested));

        if ($nested) {
            dispatch_sync(new WriteEntityTreeBuilder($addon, $slug, $namespace));
        }

        if (!$nested) {
            dispatch_sync(new WriteEntityTableBuilder($addon, $slug, $namespace));
        }

        dispatch_sync(new WriteEntityModelInterface($addon, $slug, $namespace));
        dispatch_sync(new WriteEntityRepositoryInterface($addon, $slug, $namespace));

        // Run this last since it scans the above.
        dispatch_sync(new WriteEntityTestCases($addon, $slug, $namespace));

        // Modify existing addon classes.
        dispatch_sync(new AppendEntityBindings($addon, $slug, $namespace));
        dispatch_sync(new AppendEntitySingletons($addon, $slug, $namespace));

        // Write Permissions.
        dispatch_sync(new AppendEntityStreamLang($addon, $slug));
        dispatch_sync(new AppendEntityPermissions($addon, $slug));
        dispatch_sync(new AppendEntityPermissionLang($addon, $slug));

        // Module Specific.
        if ($addon->getType() == 'module') {

            dispatch_sync(new AppendEntityRoutes($addon, $slug, $namespace));
            dispatch_sync(new AppendEntitySection($addon, $slug, $namespace));

            dispatch_sync(new AppendEntityButtonLang($addon, $slug));
            dispatch_sync(new AppendEntitySectionLang($addon, $slug));
        }

        $this->call(
            'make:migration',
            [
                'name'     => 'create_' . $slug . '_stream',
                '--addon'  => $addon->getNamespace(),
                '--stream' => $slug,
            ]
        );
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['slug', InputArgument::REQUIRED, 'The entity\'s stream slug.'],
            ['addon', InputArgument::REQUIRED, 'The addon in which to put the new entity namespace.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['namespace', null, InputOption::VALUE_OPTIONAL, 'The stream namespace if not the same as the addon.'],
            ['migration', null, InputOption::VALUE_NONE, 'Indicates if an stream migration should be created.'],
            [
                'nested',
                null,
                InputOption::VALUE_NONE,
                'Indicates if a nested builder should be created, instead of table.',
            ],
        ];
    }
}
