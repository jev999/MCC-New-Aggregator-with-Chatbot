<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Announcement;
use App\Models\Event;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get comments for a specific content item.
     */
    public function getComments($type, $id)
    {
        try {
            // Get the commentable model
            $commentableModel = $this->getCommentableModel($type, $id);

            if (!$commentableModel) {
                return response()->json(['error' => 'Content not found'], 404);
            }

            // Check if user can view this content
            $user = Auth::user();
            if (!$commentableModel->isVisibleToUser($user)) {
                return response()->json(['error' => 'Unauthorized to view comments'], 403);
            }

            // Log the request for debugging
            \Log::info('Loading comments for specific content', [
                'type' => $type,
                'id' => $id,
                'model_class' => get_class($commentableModel),
                'model_id' => $commentableModel->id,
                'user_id' => $user->id
            ]);

            // Get comments with user information, filtered based on content visibility scope
            $commentsQuery = Comment::where('commentable_type', get_class($commentableModel))
                ->where('commentable_id', $commentableModel->id)
                ->whereNull('parent_id') // Only top-level comments
                ->with(['user' => function($query) {
                    $query->select('id', 'first_name', 'middle_name', 'surname', 'role', 'department');
                }, 'replies' => function($query) {
                    $query->with(['user' => function($subQuery) {
                        $subQuery->select('id', 'first_name', 'middle_name', 'surname', 'role', 'department');
                    }]);
                }]);

            // Apply comment visibility based on content targeting
            if ($commentableModel->visibility_scope === 'all' || 
                $commentableModel->visibility_scope === null || 
                $commentableModel->visibility_scope === '') {
                // Content is for all departments - show all comments from all departments
                $commentsQuery->whereHas('user', function($query) {
                    // Show comments from all students and admins
                    $query->where('role', 'student')
                          ->orWhere('role', 'like', '%admin%');
                });
            } else {
                // Content is department/office specific - show only comments from same department + admins
                $commentsQuery->whereHas('user', function($query) use ($user) {
                    // Show comments from same department or from admins
                    $query->where('department', $user->department)
                          ->orWhere('role', 'like', '%admin%');
                });
            }

            $comments = $commentsQuery->orderBy('created_at', 'desc')->get();

            $formattedComments = $comments->map(function($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user_id' => $comment->user_id,
                    'user' => [
                        'id' => $comment->user->id,
                        'first_name' => $comment->user->first_name,
                        'middle_name' => $comment->user->middle_name,
                        'surname' => $comment->user->surname,
                        'role' => $comment->user->role,
                        'department' => $comment->user->department
                    ],
                    'time_ago' => $comment->time_ago,
                    'created_at' => $comment->created_at->toISOString(),
                    'parent_id' => $comment->parent_id,
                    'replies' => $comment->replies->map(function($reply) {
                        return [
                            'id' => $reply->id,
                            'content' => $reply->content,
                            'user_id' => $reply->user_id,
                            'user' => [
                                'id' => $reply->user->id,
                                'first_name' => $reply->user->first_name,
                                'middle_name' => $reply->user->middle_name,
                                'surname' => $reply->user->surname,
                                'role' => $reply->user->role,
                                'department' => $reply->user->department
                            ],
                            'time_ago' => $reply->time_ago,
                            'created_at' => $reply->created_at->toISOString(),
                            'parent_id' => $reply->parent_id,
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'comments' => $formattedComments
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to load comments: ' . $e->getMessage(), [
                'type' => $type,
                'id' => $id,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to load comments',
                'comments' => []
            ], 500);
        }
    }

    /**
     * Store a new comment.
     */
    public function store(Request $request)
    {
        \Log::info('Comment store method called', [
            'request_data' => $request->all(),
            'user_id' => Auth::id(),
            'user_authenticated' => Auth::check(),
            'headers' => $request->headers->all()
        ]);

        try {
            $request->validate([
                'content' => 'required|string|max:1000',
                'content_type' => 'required|in:announcement,event,news',
                'content_id' => 'required|integer',
                'parent_id' => 'nullable|exists:comments,id',
            ]);

            // Get the commentable model
            $commentableModel = $this->getCommentableModel($request->content_type, $request->content_id);

            if (!$commentableModel) {
                return response()->json(['error' => 'Content not found'], 404);
            }

            // Check if content is published
            if (!$commentableModel->is_published) {
                return response()->json(['error' => 'Cannot comment on unpublished content'], 403);
            }

            // Check if user can view this content (using existing visibility logic)
            $user = Auth::user();
            if (!$commentableModel->isVisibleToUser($user)) {
                return response()->json(['error' => 'Unauthorized to comment on this content'], 403);
            }

            // Create the comment
            $comment = Comment::create([
                'content' => $request->content,
                'user_id' => $user->id,
                'commentable_type' => get_class($commentableModel),
                'commentable_id' => $commentableModel->id,
                'parent_id' => $request->parent_id,
            ]);

            // Log the comment creation for debugging
            \Log::info('Comment created for specific content', [
                'comment_id' => $comment->id,
                'content_type' => $request->content_type,
                'content_id' => $request->content_id,
                'commentable_type' => get_class($commentableModel),
                'commentable_id' => $commentableModel->id,
                'user_id' => $user->id,
                'parent_id' => $request->parent_id
            ]);

            // Load the user relationship
            $comment->load('user');

            return response()->json([
                'success' => true,
                'comment' => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user_id' => $comment->user_id,
                    'user' => [
                        'id' => $comment->user->id,
                        'first_name' => $comment->user->first_name,
                        'middle_name' => $comment->user->middle_name,
                        'surname' => $comment->user->surname,
                        'role' => $comment->user->role,
                        'department' => $comment->user->department
                    ],
                    'time_ago' => $comment->time_ago,
                    'created_at' => $comment->created_at->toISOString(),
                    'parent_id' => $comment->parent_id,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Comment validation failed', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Comment creation failed: ' . $e->getMessage(), [
                'request' => $request->all(),
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Failed to create comment. Please try again.',
                'debug' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update a comment.
     */
    public function update(Request $request, Comment $comment)
    {
        $user = Auth::user();
        
        if (!$comment->canEdit($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment->update([
            'content' => $request->content,
        ]);

        return response()->json([
            'success' => true,
            'comment' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'time_ago' => $comment->time_ago,
            ]
        ]);
    }

    /**
     * Delete a comment.
     */
    public function destroy(Comment $comment)
    {
        $user = Auth::user();
        
        if (!$comment->canDelete($user)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Get the commentable model instance.
     */
    private function getCommentableModel($type, $id)
    {
        switch ($type) {
            case 'announcement':
                return Announcement::find($id);
            case 'event':
                return Event::find($id);
            case 'news':
                return News::find($id);
            default:
                return null;
        }
    }
}
