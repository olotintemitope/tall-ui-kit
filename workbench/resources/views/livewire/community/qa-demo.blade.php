{{-- Workbench /community: QaDemo, flat answers with likes and an accepted answer. --}}
<div class="min-w-0" data-qa-demo>
    <x-ui.post key="q1" author="Ada Obi" author-badge="Learner" time="2026-11-16 10:30:00" :now="$now"
        title="When do I post from the cash book to the ledger?" status="Solved" title-href="#qa-demo"
        body="Lesson 2 posts daily, lesson 3 at month end. Which one should we follow?"
        :likes="7" like-label="Upvote" like-icon="arrow-big-up" :comments="count($answers)" comment-label="Answers" comment-href="#qa-demo" />
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
