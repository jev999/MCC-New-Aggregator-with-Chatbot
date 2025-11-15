<?php

namespace App\Http\Controllers;

use App\Models\ShareableLink;
use Illuminate\Http\Request;

class ShareableContentController extends Controller
{
    /**
     * Display shared content using token
     */
    public function show($token)
    {
        // Find valid shareable link
        $shareableLink = ShareableLink::findValidLink($token);

        if (!$shareableLink) {
            abort(404, 'Share link not found or expired');
        }

        // Get the content
        $content = $shareableLink->getContent();

        if (!$content || !$content->is_published) {
            abort(404, 'Content not found or no longer available');
        }

        // Record access
        $shareableLink->recordAccess();

        // Load relationships
        $content->load(['admin', 'comments.user', 'comments.replies.user']);

        // Determine content type for view
        $contentType = $shareableLink->content_type;

        return view('shared.content', [
            'content' => $content,
            'contentType' => $contentType,
            'shareableLink' => $shareableLink,
        ]);
    }
}
