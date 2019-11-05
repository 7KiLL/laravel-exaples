<?php

namespace App\Http\Controllers\API\V1\Common;

use App\Http\Controllers\API\V1\Attachments\GeoController;
use App\Http\Controllers\API\V1\Attachments\LinkController;
use App\Http\Controllers\API\V1\Attachments\PictureController;
use App\Http\Controllers\API\V1\Attachments\VideoController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Common\Emoji\StoreEmoji;
use App\Http\Requests\Common\Timeline\CommentTimeline;
use App\Http\Requests\Common\Timeline\StoreCompany;
use App\Http\Requests\Common\Timeline\StoreTimeline;
use App\Http\Requests\Common\Timeline\UpdateTimeline;
use App\Http\Requests\Timeline\AddAttachments;
use App\Http\Resources\Common\CommentResource;
use App\Http\Resources\Common\EmojiResource;
use App\Http\Resources\Common\TimelineResource;
use App\Models\Actors\Company;
use App\Models\Actors\User;
use App\Models\Common\Comment;
use App\Models\Common\Timeline;
use Exception;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class TimelineController extends Controller
{
    /**
     * @var $user User
     */
    private $user;

    public function __construct()
    {
        $this->user = auth()->user();
        $this->authorizeResource(Timeline::class);

    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        return TimelineResource::collection(
            $this->user->timeline()
                ->with(['pictures', 'videos', 'geo', 'links'])
                ->paginate(request()->get('per_page') ?? 15)->appends(request()->all())
        );
    }

    /**
     * Returns all timelines for current user's company
     *
     * @return AnonymousResourceCollection
     */
    public function indexCompany()
    {
        return TimelineResource::collection(
            $this->user->company->timeline()
                ->with(['pictures', 'videos', 'geo', 'links'])
                ->paginate(request()->get('per_page') ?? 15)->appends(request()->all())
        );
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param StoreTimeline $request
     * @return TimelineResource
     */
    public function store(StoreTimeline $request)
    {
        $user = User::findOrFail($request->get('user_id'));
        /** @var Timeline $timeline */
        $timeline = $user->timeline()->save(Timeline::make([
            'message' => $request->get('message'),
            'author_id' => $this->user->id
        ]));
        if ($request->has('pictures')) {
            PictureController::store($timeline, $request->pictures);
        }
        if ($request->has('videos')) {
            VideoController::store($timeline, $request->videos);
        }
        if ($request->has('links')) {
            LinkController::store($timeline, $request->links);
        }
        if ($request->has('geo')) {
            GeoController::store($timeline, $request->geo);
        }
        $timeline->refresh();
        return TimelineResource::make($timeline->load(['pictures', 'videos', 'links', 'geo']));
    }

    /**
     * Store timeline to company model
     *
     * @param StoreCompany $request
     * @return TimelineResource
     */
    public function storeCompany(StoreCompany $request)
    {
        $company = Company::findOrFail($request->get('company_id'));
        /** @var Timeline $timeline */
        $timeline = $company->timeline()->save(Timeline::make([
            'message' => $request->get('message'),
            'author_id' => $this->user->id
        ]));
        if ($request->has('pictures')) {
            PictureController::store($timeline, $request->pictures);
        }
        if ($request->has('videos')) {
            VideoController::store($timeline, $request->videos);
        }
        if ($request->has('links')) {
            LinkController::store($timeline, $request->links);
        }
        if ($request->has('geo')) {
            GeoController::store($timeline, $request->geo);
        }
        $timeline->refresh();
        return TimelineResource::make($timeline->load(['pictures', 'videos', 'links', 'geo']));
    }

    /**
     * Display the specified resource.
     *
     * @param Timeline $timeline
     * @return AnonymousResourceCollection
     */
    public function show(Timeline $timeline)
    {
        return TimelineResource::make($timeline->loadMissing(['pictures', 'videos', 'links', 'geo', 'comments']));
    }

    /**
     * Get timeline for certain company
     *
     * @param Company $company
     * @return AnonymousResourceCollection
     */
    public function getCompany(Company $company)
    {
        return TimelineResource::collection($company->timeline()->paginate());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTimeline $request
     * @param Timeline $timeline
     * @return Response
     */
    public function update(UpdateTimeline $request, Timeline $timeline)
    {
        if ($request->has('pictures')) {
            PictureController::update($timeline, $request->pictures);
        }
        if ($request->has('videos')) {
            VideoController::update($timeline, $request->videos);
        }
        if ($request->has('links')) {
            LinkController::update($timeline, $request->links);
        }
        if ($request->has('geo')) {
            GeoController::update($timeline, $request->geo);
        }
        $timeline->refresh();
        return TimelineResource::make($timeline->load(['pictures', 'videos', 'links', 'geo']));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Timeline $timeline
     * @return Response
     * @throws Exception
     */
    public function destroy(Timeline $timeline)
    {
        $timeline->delete();
        return $this->json('', 204);
    }

    /**
     * @param StoreEmoji $emoji
     * @param Timeline $timeline
     * @return AnonymousResourceCollection
     */
    public function emote(StoreEmoji $emoji, Timeline $timeline)
    {
        EmojiController::store($emoji, $timeline);
        $timeline->refresh();
        return EmojiResource::collection($timeline->emoji);
    }

    /**
     * @param CommentTimeline $comment
     * @param Timeline $timeline
     * @return AnonymousResourceCollection
     */
    public function comment(CommentTimeline $comment, Timeline $timeline)
    {
        CommentController::store($timeline, Comment::make([
            'comment' => $comment->get('comment'),
            'author_id' => $this->user->id,
            'reply_to' => $comment->get('reply_to') ?? null
        ]));
        $timeline->refresh();
        return CommentResource::collection($timeline->comments);
    }

    /**
     * @param AddAttachments $attachments
     * @param Timeline $timeline
     * @return TimelineResource
     */
    public function attach(AddAttachments $attachments, Timeline $timeline)
    {
        if ($attachments->has('pictures')) {
            PictureController::store($timeline, $attachments->pictures);
        }
        if ($attachments->has('videos')) {
            VideoController::store($timeline, $attachments->videos);
        }
        if ($attachments->has('links')) {
            LinkController::store($timeline, $attachments->links);
        }
        if ($attachments->has('geo')) {
            GeoController::store($timeline, $attachments->geo);
        }
        $timeline->refresh();
        return TimelineResource::make($timeline->loadMissing(['pictures', 'videos', 'geo', 'links']));
    }

    /**
     * @param AddAttachments $attachments
     * @param Timeline $timeline
     * @return TimelineResource
     */
    public function reattach(AddAttachments $attachments, Timeline $timeline)
    {
        if ($attachments->has('pictures')) {
            PictureController::update($timeline, $attachments->pictures);
        }
        if ($attachments->has('videos')) {
            VideoController::update($timeline, $attachments->videos);
        }
        if ($attachments->has('links')) {
            LinkController::update($timeline, $attachments->links);
        }
        if ($attachments->has('geo')) {
            GeoController::update($timeline, $attachments->geo);
        }
        $timeline->refresh();
        return TimelineResource::make($timeline->loadMissing(['pictures', 'videos', 'geo', 'links']));
    }
}
