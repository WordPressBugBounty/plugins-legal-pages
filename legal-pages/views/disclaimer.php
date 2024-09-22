<div class="wplp-container-sm wplp-ml-10">
    <h1 class="wplp-page-title">DISCLAIMER</h1>
    <div class="wplp-disclaimer-wrap">
        <form action="" method="post">
            <?php wp_nonce_field( 'adl_lp_accept_terms_nonce', 'adl_lp_accept_terms_nonce_field' ); ?>
            <textarea rows="20" cols="130">wpwax.com ("Site") and the documents or pages that it may provide, are provided on the condition that you accept these terms, and any other terms or disclaimers that we may provide. You may not use or post any of the templates or legal documents until and unless you agreed. We are not licensed attorneys and do not purport to be. wpwax.com is not a law firm, is not comprised of a law firm, and its employees are not lawyers. We do not review your site and we will not review your site. We do not purport to act as your attorney and do not make any claims that would constitute legal advice. We do not practice law in any state, nor are any of the documents provided via our Site intended to be in lieu of receiving legal advice. The information we may provide is general in nature, and may be different in your jurisdiction. In other words, do not take these documents to be "bulletproof" or to give you protection from lawsuits. They are not a substitute for legal advice and you should have an attorney review them. Accordingly, we disclaim any and all liability and make no warranties, including disclaimer of warranty for implied purpose, merchantability, or fitness for a particular purpose. We provide these documents on an as is basis, and offer no express or implied warranties. The use of our plugin and its related documents is not intended to create any representation or approval of the legality of your site and you may not represent it as such. We will have no responsibility or liability for any claim of loss, injury, or damages related to your use or reliance on these documents, or any third parties use or reliance on these documents. They are to be used at your own risk. Your only remedy for any loss or dissatisfaction with LEGAL PAGES is to discontinue your use of the service and remove any documents you may have downloaded. To the degree that we have had a licensed attorney review these documents it is for our own internal purposes and you may not rely on this as legal advice. Since the law is different in every state, you should have these documents reviewed by an attorney in your jurisdiction. As stated below, we disclaim any and all liability and warranties, including damages or loss that may result from your use or misuse of the documents. Unless prohibited or limited by law, our damages in any matter are limited to the amount you paid for the LEGAL PAGES plugin.</textarea>
            <br/>
            <br/>
            <div class="wplp-checkbox">

                <input type="checkbox" name="adl_accept_terms" value="1" id="adl_accept_terms" <?php if(isset($lpterms) && $lpterms==1) { echo "checked"; }?> onclick="jQuery('#adl_lp_submit').toggle();">

                <label for="adl_accept_terms" class="wplp-checkbox__label">You need to accept the disclaimer to use this plugin</label>

            </div>

            <br/>
            <br/>
            <input type="submit" name="adl_lp_submit" id="adl_lp_submit" class="wplp-btn-primary btn btn-primary" style="display:none;" value="Accept" /> </form>
    </div>

</div>
