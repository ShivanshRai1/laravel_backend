<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\BlogPost;

class BlogPostStatusChanged extends Notification
{
    use Queueable;

    protected $blogPost;
    protected $action;
    protected $reason;
    protected $adminNotes;
    protected $reviewerName;

    public function __construct(BlogPost $blogPost, $action, $reason = null, $adminNotes = null, $reviewerName = null)
    {
        $this->blogPost = $blogPost;
        $this->action = $action; // 'approved', 'rejected', 'changes_requested'
        $this->reason = $reason;
        $this->adminNotes = $adminNotes;
        $this->reviewerName = $reviewerName;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $data = [
            'blog_post_id' => $this->blogPost->id,
            'blog_post_title' => $this->blogPost->title,
            'action' => $this->action,
            'reviewer_name' => $this->reviewerName,
            'reviewed_at' => now()->toDateTimeString(),
        ];

        if ($this->action === 'approved') {
            $data['message'] = "Your blog post \"{$this->blogPost->title}\" has been approved and published!";
            $data['type'] = 'success';
        } elseif ($this->action === 'rejected') {
            $data['message'] = "Your blog post \"{$this->blogPost->title}\" has been rejected.";
            $data['reason'] = $this->reason;
            $data['type'] = 'error';
        } elseif ($this->action === 'changes_requested') {
            $data['message'] = "Changes requested for your blog post \"{$this->blogPost->title}\".";
            $data['changes_requested'] = $this->reason;
            $data['type'] = 'warning';
        }

        if ($this->adminNotes) {
            $data['admin_notes'] = $this->adminNotes;
        }

        return $data;
    }
}
