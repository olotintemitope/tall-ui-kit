{{-- Workbench /community: QaDemo, flat answers with likes and an accepted answer. --}}
<div class="min-w-0" data-qa-demo>
    <x-ui.comment-thread
        :comments="$answers"
        label="Answers"
        :nested="false"
        reply-action="answer"
        like-action="like"
        like-label="Helpful"
        like-icon="thumbs-up"
        accept-action="accept"
        add-label="Your answer"
        add-placeholder="Write an answer"
        add-submit-label="Post answer"
        empty="No answers yet."
        :now="$now"
        id="qa-demo"
    />
</div>
