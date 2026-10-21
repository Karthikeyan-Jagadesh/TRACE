<?php
use App\Helpers\Helper;
?>
<footer class="trace-footer">
    <div class="trace-container">
        <div class="footer-top">
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <span class="brand-symbol">T</span>
                    <span class="brand-text">TRACE</span>
                </div>
                <p class="text-sm max-w-sm text-gray-400 mb-4">
                    Every lost thing leaves a trace. A community-powered recovery network reconnecting lost belongings through explainable spatial intelligence.
                </p>
                <div class="font-mono text-xs text-lime-400 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-lime-400 animate-pulse"></span>
                    Operational &bull; Community Network Active
                </div>
            </div>

            <div>
                <div class="footer-col-title">Network Discovery</div>
                <ul class="footer-links">
                    <li><a href="/search">All Public Reports</a></li>
                    <li><a href="/search?type=lost">Lost Belongings</a></li>
                    <li><a href="/search?type=found">Recoverable Found Items</a></li>
                    <li><a href="/map">Spatial Map Intelligence</a></li>
                </ul>
            </div>

            <div>
                <div class="footer-col-title">How It Works</div>
                <ul class="footer-links">
                    <li><a href="/reports/create/lost">Report a Lost Item</a></li>
                    <li><a href="/reports/create/found">Report a Found Item</a></li>
                    <li><a href="/#how-it-works">Smart Matching Signal</a></li>
                    <li><a href="/#how-it-works">Ownership Verification</a></li>
                </ul>
            </div>

            <div>
                <div class="footer-col-title">Trust & Safety</div>
                <ul class="footer-links">
                    <li><a href="/#guidelines">Safe Collection Guidelines</a></li>
                    <li><a href="/#privacy">Privacy-Preserving Custody</a></li>
                    <li><a href="/#terms">Terms of Community Service</a></li>
                    <li><a href="/#contact">Campus & Office Deployments</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <div>
                &copy; <?= date('Y') ?> TRACE &mdash; Lost &amp; Found Intelligence Network. Crafted for community recovery.
            </div>
            <div class="flex items-center gap-4">
                <span>Zero Paid APIs</span>
                <span>Deterministic Scoring</span>
                <span>Spatial Editorial</span>
            </div>
        </div>
    </div>
</footer>
