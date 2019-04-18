<?php namespace Rikki\LoungeViews\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use Indikator\Content\Components\BlogPage as origin;

class BlogPage extends origin
{
    public function componentDetails()
    {
        return [
            'name'        => 'BlogPage',
            'description' => 'Shows a BlogPost'
        ];
    }

    public function previousPost()
    {
        return $this->getPostSibling(-1);
    }

    public function nextPost()
    {
        return $this->getPostSibling(1);
    }

    protected function getPostSibling($direction = 1)
    {
        if (!$this->post) {
            return;
        }

        $method = $direction === -1 ? 'previousPost' : 'nextPost';

        if (!$post = $this->post->$method()) {
            return;
        }

        $postPage = $this->getPage()->getBaseFileName();

        $post->setUrl($postPage, $this->controller);

        $post->categories->each(function($category) {
            $category->setUrl($this->categoryPage, $this->controller);
        });

        return $post;
    }
}
