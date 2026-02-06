<div class="wplp-container-sm wplp-ml-10">
    <h1 class="wplp-page-title">DISCLAIMER</h1>

    <div class="wplp-disclaimer-wrap">
        <form action="" method="post">
            <?php wp_nonce_field( 'adl_lp_accept_terms_nonce', 'adl_lp_accept_terms_nonce_field' ); ?>

            <div class="wplp-disclaimer-content">
                <h3>General Terms</h3>
                <p>
                    wpwax.com (“Site”) and the documents or pages that it may provide are offered
                    on the condition that you accept these terms and any other disclaimers we provide.
                </p>

                <p>
                    You may not use or post any templates or legal documents unless and until you
                    agree to these terms.
                </p>

                <h3>No Legal Advice</h3>
                <p>
                    We are not licensed attorneys and do not purport to be. wpwax.com is not a law firm,
                    is not comprised of a law firm, and its employees are not lawyers.
                </p>

                <p>
                    We do not review your site and do not act as your attorney. Nothing provided
                    constitutes legal advice.
                </p>

                <h3>General Information Only</h3>
                <p>
                    The information provided is general in nature and may differ by jurisdiction.
                    These documents should not be considered “bulletproof” or a substitute for
                    professional legal advice.
                </p>

                <h3>No Warranties</h3>
                <p>
                    All documents are provided on an <strong>“as is”</strong> basis without express or implied
                    warranties, including merchantability or fitness for a particular purpose.
                </p>

                <h3>Limitation of Liability</h3>
                <p>
                    We are not responsible for any loss, injury, or damages arising from your use
                    or reliance on these documents, including use by third parties.
                </p>

                <p>
                    Your sole remedy is to discontinue use of the LEGAL PAGES service and remove
                    any downloaded documents.
                </p>

                <h3>Damage Limitation</h3>
                <p>
                    Unless prohibited by law, any damages are limited to the amount paid for the
                    LEGAL PAGES plugin.
                </p>
            </div>

            <div class="wplp-checkbox">
                <input
                    type="checkbox"
                    name="adl_accept_terms"
                    value="1"
                    id="adl_accept_terms"
                    <?php if ( isset( $lpterms ) && $lpterms == 1 ) echo 'checked'; ?>
                    onclick="jQuery('#adl_lp_submit').toggle();"
                >
                <label for="adl_accept_terms" class="wplp-checkbox__label">
                    You must accept the disclaimer to use this plugin
                </label>
            </div>

            <input
                type="submit"
                name="adl_lp_submit"
                id="adl_lp_submit"
                class="wplp-btn-primary btn btn-primary"
                style="display:none;"
                value="Accept"
            />
        </form>
    </div>
</div>
