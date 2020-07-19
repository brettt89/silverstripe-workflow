# Using Workflows within SilverStripe

Your Workflow can be accessed from anywher inside of a SilverStripe application, so usage of Workflows can be expanded on almost anywhere. Below is a example of how a Workflow might be used when publishing a comment on a blog article.

```
namespace MyApp;

use SilverStripe\ORM\DataObject;
use SilverStripe\Workflow\WorkflowService;

class BlogArtice extends DataObject
{   
    // ...

    public function publishComment(Comment $comment)
    {
        $workflow = WorkflowService::registry()->get($comment);

        // Update the currentState on the post
        try {
            $workflow->apply($post, 'publish');
        } catch (LogicException $exception) {
            // ...
        }
        // ...
    }

    public function rejectComment(Comment $comment)
    {
        $workflow = WorkflowService::registry()->get($comment);

        // Update the currentState on the post
        try {
            $workflow->apply($post, 'reject');
        } catch (LogicException $exception) {
            // ...
        }
        // ...
    }
}

```