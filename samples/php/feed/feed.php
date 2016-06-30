<?php

public function feed()
{
    $posts = Post::query();
    collect($this->following)->groupBy('followable_type')->each(function($item, $key) use($posts){
        $ids = collect($item)->pluck('followable_id')->all();
        $type = strtolower(collect(explode('\\', $key))->last());
        $posts->orWhereIn($type . '_id', $ids);
    });

    return $posts;
}
