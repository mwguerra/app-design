<?php

namespace MWGuerra\AppDesign\Generators;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class LayoutGenerator extends Generator
{
    protected $resources;

    public function __construct(array $resources)
    {
        parent::__construct();

        $this->resources = $resources;
    }

    public function generate(): array
    {
        $menuLinks = $this->generateMenuLinks($this->resources);

        $layoutStubFilePath = $this->packageBasePath . config('appdesign.paths.stubs.layouts') . '/Jetstream/AppLayout.vue.stub';
        $destinationFilePath = config('appdesign.paths.layouts') . '/AppLayout.vue';

        $this->replaceInStubAndSave($layoutStubFilePath, $destinationFilePath, [
            '##navigation_links##' => $menuLinks['mainMenuLinks'],
            '##responsive_navigation_links##' => $menuLinks['mainMenuResponsiveLinks'],
            '##user_menu_navigation_links##' => $menuLinks['userMenuLinks'],
            '##user_menu_responsive_navigation_links##' => $menuLinks['userMenuLinks'],
        ]);

        return [
            'log' => $this->getLog(),
            'files' => $this->getFiles(),
        ];
    }

    protected function generateMenuLinks(array $resources): array {
        $mainMenuLinks = '';
        $mainMenuResponsiveLinks = '';
        $userMenuLinks = '';

        foreach ($this->resources as $resource) {
            if (isset($resource['showInMenu']) && $resource['showInMenu'] !== true) {
                continue;
            }

            $modelName = Str::studly($resource['name']);
            $routeName = Str::kebab(Str::plural($modelName));

            if (isset($resource['menuType']) && $resource['menuType'] === 'main') {
                $mainMenuLinks .= "<NavLink :href=\"route('$routeName.index')\" :active=\"route().current('$routeName.*')\">$modelName</NavLink>\n";
                $mainMenuResponsiveLinks .= "<ResponsiveNavLink :href=\"route('$routeName.index')\" :active=\"route().current('$routeName.*')\">$modelName</ResponsiveNavLink>\n";
            }

            if (isset($resource['menuType']) && $resource['menuType'] === 'user') {
                $mainMenuLinks .= "<DropdownLink :href=\"route('$routeName.index')\">$modelName</DropdownLink>\n";
            }
        }

        return [
            'mainMenuLinks' => $mainMenuLinks,
            'mainMenuResponsiveLinks' => $mainMenuResponsiveLinks,
            'userMenuLinks' => $userMenuLinks,
        ];
    }
}
