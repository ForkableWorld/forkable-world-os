<?php /** @var \App\Studies\QMStudy $page */ ?>
<?php ($page = $page ?? $post ?? $model ?? $obj ?? $r ?? $v ?? null); ?>
<div class="page-share">
    <a
        href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($page->getUrl()) }}"
        onclick="window.open(this.href, 'window', 'left=20,top=20,width=500,height=500,toolbar=1,resizable=0'); return false;"
        class="btn btn--facebook btn--small"
    >
        <i class="fab fa-fw fa-facebook" aria-hidden="true"></i>
        <span>Share</span></a>
    <a
        href="https://twitter.com/intent/tweet?text={{ urlencode($page->getTitleAttribute()) }}%20{{ urlencode($page->getUrl()) }}"
        onclick="window.open(this.href, 'window', 'left=20,top=20,width=500,height=500,toolbar=1,resizable=0'); return false;"
        class="btn btn--twitter btn--small"
    >
        <i class="fab fa-fw fa-twitter" aria-hidden="true"></i>
        <span>Tweet</span></a>
    <a
        href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode($page->getUrl()) }}"
        onclick="window.open(this.href, 'window', 'left=20,top=20,width=500,height=500,toolbar=1,resizable=0'); return false;"
        class="btn btn--linkedin btn--small"
    >
        <i class="fab fa-fw fa-linkedin" aria-hidden="true"></i>
        <span>LinkedIn</span></a>
    <a
        href="https://reddit.com/submit?title={{ urlencode($page->getTitleAttribute()) }}&url={{ urlencode($page->getUrl()) }}"
        onclick="window.open(this.href, 'window', 'left=20,top=20,width=500,height=500,toolbar=1,resizable=0'); return false;"
        class="btn btn--reddit btn--small"
    >
        <i class="fab fa-fw fa-reddit" aria-hidden="true"></i>
        <span>Reddit</span></a>
</div>
