<?php namespace Rikki\LoungeViews\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use Indikator\Content\Models\Blog as ItemPost;


class BlogFeatured extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Blog Featured',
            'description' => 'Shows all featured Blogposts'
        ];
    }

    public $posts;

    public function onRun()
    {
        $this->posts = $this->page['posts'] = $this->listPosts();
    }

    protected function listPosts()
    {
        $posts = ItemPost::with('categories')->where('featured',true)->listFrontEnd([
            'page'     => 1,
            'perPage'  => 5
        ]);

        $posts->each(function($post) {
            $post->setUrl('blog/post', $this->controller);
        });

        return $posts;
    }
}
