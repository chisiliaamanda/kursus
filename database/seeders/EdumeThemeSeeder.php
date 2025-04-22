<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Appearance\Entities\Theme;

class EdumeThemeSeeder extends Seeder
{

    public function run()
    {
        try {
            Theme::insert(
                [
                    'user_id' => 1,
                    'name' => 'edume',
                    'title' => 'Edume Theme',
                    'image' => 'public/frontend/edume/img/edume.jpg',
                    'version' => '1.0.0',
                    'folder_path' => 'edume',
                    'live_link' => '#',
                    'description' => 'Edume is a new premium theme for infix LMS',
                    'is_active' => 0,
                    'status' => 1,
                    'tags' => 'tags, tags, tags',

                ]

            );
        } catch (\Exception $exception) {

        };
//        \Illuminate\Support\Facades\DB::statement("INSERT INTO `themes` (`user_id`, `name`, `title`, `image`, `version`, `folder_path`, `live_link`, `description`, `is_active`, `status`, `tags`) VALUES
//(1, 'edume', 'Edume Theme', 'public/frontend/edume/img/edume.jpg', '1.0.0', 'edume', '#', 'Edume is a new premium theme for infix LMS', 0, 1, 'tags, tags, tags')");
    }
}
