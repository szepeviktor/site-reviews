<div class="glsr-card postbox">
    <h3 class="glsr-card-heading">
        <button type="button" class="glsr-accordion-trigger" aria-expanded="false" aria-controls="faq-add-review-pagination">
            <span class="title">How do I add pagination to my reviews?</span>
            <span class="icon"></span>
        </button>
    </h3>
    <div id="faq-add-review-pagination" class="inside">
        <p>To paginate your reviews (i.e. split them up into multiple pages), simply use the "Pagination" setting together with the "Review Count" setting in the Editor Block.</p>
        <p>If you are using the shortcodes, then use the <code>pagination</code> and <code>count</code> options.</p>
        <p>For example, this will paginate reviews to 10 reviews per-page:</p>
        <pre><code class="language-html">[site_reviews pagination=ajax count=10]</code></pre>
        <p>To lean more about the available shortcode options and how to use them, please see the <code><a href="<?= admin_url('edit.php?post_type='.glsr()->post_type.'&page=documentation#tab-shortcodes'); ?>">Documentation > Shortcodes</a></code> page.</p>
    </div>
</div>
