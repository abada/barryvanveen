<?php

use Barryvanveen\Blogs\Blog;
use Barryvanveen\Comments\Comment;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CommentTest extends TestCase
{
    use DatabaseTransactions;

    public function testViewOnlineCommentsWithBlog()
    {
        /** @var Blog $blog */
        $blog = factory(Barryvanveen\Blogs\Blog::class, 'published')->create();

        $online_comments = factory(Barryvanveen\Comments\Comment::class, 'online', 3)->create(
            [
                'blog_id' => $blog->id
            ]
        );

        $offline_comments = factory(Barryvanveen\Comments\Comment::class, 'offline', 3)->create(
            [
                'blog_id' => $blog->id
            ]
        );

        $this->visit(route('blog-item', ['id' => $blog->id, 'slug' => $blog->slug]))
            ->see(trans('comments.title'));

        /** @var Comment $comment */
        foreach ($online_comments as $comment) {
            $this->see($comment->text);
        }

        foreach ($offline_comments as $comment) {
            $this->dontSee($comment->text);
        }
    }

    public function testViewBlogWithoutComments()
    {
        /** @var Blog $blog */
        $blog = factory(Barryvanveen\Blogs\Blog::class, 'published')->create();

        $this->visit(route('blog-item', ['id' => $blog->id, 'slug' => $blog->slug]))
            ->see(trans('comments.title'))
            ->see(trans('comments.no-comments'));
    }

    public function testPostNewComment()
    {
        /** @var Blog $blog */
        $blog = factory(Barryvanveen\Blogs\Blog::class, 'published')->create();

        $new_comment = 'my newest comment';

        $this->visit(route('blog-item', ['id' => $blog->id, 'slug' => $blog->slug]))
            ->see(trans('comments.add-your-comment'))
            ->type('John Doe', 'name')
            ->type('john@example.com', 'email')
            ->type($new_comment, 'text')
            ->press(trans('comments.submit'))
            ->seePageIs(route('blog-item', ['id' => $blog->id, 'slug' => $blog->slug]))
            ->see($new_comment);

    }

    public function testPostNewCommentWithFalseInformation()
    {
        /** @var Blog $blog */
        $blog = factory(Barryvanveen\Blogs\Blog::class, 'published')->create();

        try {

            $this->visit(route('blog-item', ['id' => $blog->id, 'slug' => $blog->slug]))
                 ->type('asdasd', 'email')
                 ->press(trans('comments.submit'))
                 ->seePageIs(route('blog-item', ['id' => $blog->id, 'slug' => $blog->slug]));

        } catch (Exception $e) {

            $this->assertEquals(Illuminate\Foundation\Testing\HttpException::class, get_class($e));

            return;

        }

        $this->assertTrue(
            false,
            'You shouldn\'t reach this assertion, an exception should have been thrown on form 
        validation'
        );

    }

    public function testPostNewCommentWithHoneypot()
    {
        /** @var Blog $blog */
        $blog = factory(Barryvanveen\Blogs\Blog::class, 'published')->create();

        $new_comment = 'my newest comment';

        try {

            $this->visit(route('blog-item', ['id' => $blog->id, 'slug' => $blog->slug]))
                ->see(trans('comments.add-your-comment'))
                ->type('John Doe', 'name')
                ->type('john@example.com', 'email')
                ->type($new_comment, 'text')
                ->type('ishouldnotfillthisfield', 'youshouldnotfillthisfield')
                ->press(trans('comments.submit'))
                ->seePageIs(route('blog-item', ['id' => $blog->id, 'slug' => $blog->slug]))
                ->see($new_comment);

        } catch (Exception $e) {

            $this->assertEquals(Illuminate\Foundation\Testing\HttpException::class, get_class($e));

            return;

        }

        $this->assertTrue(
            false,
            'You shouldn\'t reach this assertion, an exception should have been thrown on form 
        validation'
        );

    }


    public function testCommentsDisabled()
    {
        config(['custom.comments_enabled' => false]);

        /** @var Blog $blog */
        $blog = factory(Barryvanveen\Blogs\Blog::class, 'published')->create();

        $this->visit(route('blog-item', ['id' => $blog->id, 'slug' => $blog->slug]))
             ->see(trans('comments.comments-are-closed'));

    }
}