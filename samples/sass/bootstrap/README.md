# Extending Bootstrap in SASS

We'd been using Bootstrap as the basis for our designs for awhile when we transitioned to a SASS-based workflow, so it was important that anybody touching our themes was familiar with the Bootstrap basics. This was especially true on older themes that had the Bootstrap class names inline in the HTML.

So when we switched over to SASS, I found we worked a lot better using the @extend method rather than Bootstrap's library of mixins for a lot of our declarations. There's a readable, almost annotation-like quality to extending, and it encourages that familiarity we need for people working on our older sites.
