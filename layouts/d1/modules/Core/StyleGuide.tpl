{**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 *}
{strip}
    <main class="container-fluid px-3 px-lg-4 pb-5" id="defalto-style-guide">
        <header class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                    <div>
                        <p class="text-primary fw-semibold mb-1">Defalto UI</p>
                        <h1 class="display-6 mb-2">Style Guide</h1>
                        <p class="text-body-secondary mb-0">Live components rendered with the styles currently loaded by Defalto.</p>
                    </div>
                    <span class="badge rounded-pill text-bg-primary px-3 py-2">Bootstrap 5</span>
                </div>
            </div>
        </header>

        <nav class="card border-0 shadow-sm mb-4" aria-label="Style Guide sections">
            <div class="card-body d-flex flex-wrap gap-2">
                <a class="btn btn-sm btn-outline-secondary" href="#colors">Colors</a>
                <a class="btn btn-sm btn-outline-secondary" href="#alerts">Alerts</a>
                <a class="btn btn-sm btn-outline-secondary" href="#buttons">Buttons</a>
                <a class="btn btn-sm btn-outline-secondary" href="#badges">Badges</a>
                <a class="btn btn-sm btn-outline-secondary" href="#forms">Forms</a>
                <a class="btn btn-sm btn-outline-secondary" href="#navigation">Navigation</a>
                <a class="btn btn-sm btn-outline-secondary" href="#content">Content</a>
                <a class="btn btn-sm btn-outline-secondary" href="#feedback">Feedback</a>
            </div>
        </nav>

        <section class="mb-4" id="colors" aria-labelledby="colors-title">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h3 mb-0" id="colors-title">Colors</h2>
                <code>text-bg-*</code>
            </div>
            <div class="row g-3 mb-3">
                {foreach from=['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'] item=COLOR}
                    <div class="col-sm-6 col-lg-3">
                        <div class="text-bg-{$COLOR} rounded-3 shadow-sm p-4 h-100">
                            <div class="fw-semibold text-capitalize">{$COLOR}</div>
                            <code class="text-reset">.text-bg-{$COLOR}</code>
                        </div>
                    </div>
                {/foreach}
            </div>
            <div class="row g-3">
                {foreach from=['primary', 'secondary', 'success', 'danger', 'warning', 'info'] item=COLOR}
                    <div class="col-sm-6 col-lg-4">
                        <div class="bg-{$COLOR}-subtle text-{$COLOR}-emphasis border border-{$COLOR}-subtle rounded-3 p-3">
                            <span class="fw-semibold text-capitalize">{$COLOR} subtle</span>
                            <code class="d-block text-reset mt-1">.bg-{$COLOR}-subtle</code>
                        </div>
                    </div>
                {/foreach}
            </div>
        </section>

        <section class="mb-4" id="alerts" aria-labelledby="alerts-title">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h3 mb-0" id="alerts-title">Alerts</h2>
                <code>.alert</code>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 p-lg-4">
                    {foreach from=['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'] item=COLOR}
                        <div class="alert alert-{$COLOR} d-flex align-items-center gap-2" role="alert">
                            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                            <div><strong class="text-capitalize">{$COLOR} alert.</strong> This is a live <code>.alert-{$COLOR}</code> example.</div>
                        </div>
                    {/foreach}
                    <div class="alert alert-warning alert-dismissible fade show mb-0" role="alert">
                        <strong>Dismissible alert.</strong> Use it when the user may close the message.
                        <button class="btn-close" type="button" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-4" id="buttons" aria-labelledby="buttons-title">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h3 mb-0" id="buttons-title">Buttons</h2>
                <code>.btn</code>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 p-lg-4">
                    <h3 class="h6 text-body-secondary mb-3">Solid</h3>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        {foreach from=['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 'link'] item=COLOR}
                            <button class="btn btn-{$COLOR} text-capitalize" type="button">{$COLOR}</button>
                        {/foreach}
                    </div>
                    <h3 class="h6 text-body-secondary mb-3">Outline</h3>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        {foreach from=['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'] item=COLOR}
                            <button class="btn btn-outline-{$COLOR} text-capitalize" type="button">{$COLOR}</button>
                        {/foreach}
                    </div>
                    <h3 class="h6 text-body-secondary mb-3">Size and state</h3>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <button class="btn btn-primary btn-lg" type="button">Large</button>
                        <button class="btn btn-primary" type="button">Default</button>
                        <button class="btn btn-primary btn-sm" type="button">Small</button>
                        <button class="btn btn-primary" type="button" disabled>Disabled</button>
                        <div class="btn-group" role="group" aria-label="Example button group">
                            <button class="btn btn-outline-secondary" type="button">Left</button>
                            <button class="btn btn-outline-secondary active" type="button" aria-pressed="true">Active</button>
                            <button class="btn btn-outline-secondary" type="button">Right</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-4" id="badges" aria-labelledby="badges-title">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h3 mb-0" id="badges-title">Badges</h2>
                <code>.badge</code>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex flex-wrap align-items-center gap-2 p-3 p-lg-4">
                    {foreach from=['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'] item=COLOR}
                        <span class="badge text-bg-{$COLOR} text-capitalize">{$COLOR}</span>
                    {/foreach}
                    <span class="badge rounded-pill text-bg-primary">Pill</span>
                    <span class="badge rounded-pill bg-body-secondary text-body border">Neutral</span>
                </div>
            </div>
        </section>

        <section class="mb-4" id="forms" aria-labelledby="forms-title">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h3 mb-0" id="forms-title">Forms</h2>
                <code>.form-control</code>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 p-lg-4">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <label class="form-label" for="style-guide-text">Text input</label>
                            <input class="form-control mb-3" id="style-guide-text" type="text" value="Example value">
                            <label class="form-label" for="style-guide-select">Select</label>
                            <select class="form-select mb-3" id="style-guide-select">
                                <option selected>Selected option</option>
                                <option>Another option</option>
                            </select>
                            <label class="form-label" for="style-guide-addon">Input group</label>
                            <div class="input-group">
                                <span class="input-group-text">EUR</span>
                                <input class="form-control" id="style-guide-addon" type="text" value="120.00" aria-label="Example amount">
                                <button class="btn btn-outline-secondary" type="button">Apply</button>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-floating mb-3">
                                <input class="form-control" id="style-guide-floating" type="email" value="name@example.com" placeholder="name@example.com">
                                <label for="style-guide-floating">Floating label</label>
                            </div>
                            <label class="form-label" for="style-guide-valid">Validation</label>
                            <input class="form-control is-valid mb-1" id="style-guide-valid" type="text" value="Valid value">
                            <div class="valid-feedback d-block mb-3">Valid feedback</div>
                            <input class="form-control is-invalid mb-1" id="style-guide-invalid" type="text" value="Invalid value" aria-describedby="style-guide-invalid-feedback">
                            <div class="invalid-feedback d-block" id="style-guide-invalid-feedback">Invalid feedback</div>
                        </div>
                        <div class="col-12">
                            <div class="d-flex flex-wrap gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" id="style-guide-check" type="checkbox" checked>
                                    <label class="form-check-label" for="style-guide-check">Checkbox</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" id="style-guide-radio" name="style-guide-radio" type="radio" checked>
                                    <label class="form-check-label" for="style-guide-radio">Radio</label>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" id="style-guide-switch" type="checkbox" role="switch" checked>
                                    <label class="form-check-label" for="style-guide-switch">Switch</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-4" id="navigation" aria-labelledby="navigation-title">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h3 mb-0" id="navigation-title">Navigation</h2>
                <code>.nav</code>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 p-lg-4">
                    <ul class="nav nav-tabs mb-4">
                        <li class="nav-item"><a class="nav-link active" href="#navigation">Active</a></li>
                        <li class="nav-item"><a class="nav-link" href="#navigation">Link</a></li>
                        <li class="nav-item"><a class="nav-link disabled" href="#navigation" aria-disabled="true">Disabled</a></li>
                    </ul>
                    <ul class="nav nav-pills gap-2 mb-4">
                        <li class="nav-item"><a class="nav-link active" href="#navigation">Active</a></li>
                        <li class="nav-item"><a class="nav-link" href="#navigation">Link</a></li>
                    </ul>
                    <nav aria-label="Example breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="#navigation">Home</a></li>
                            <li class="breadcrumb-item"><a href="#navigation">Module</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Record</li>
                        </ol>
                    </nav>
                    <nav aria-label="Example pagination">
                        <ul class="pagination mb-0">
                            <li class="page-item disabled"><a class="page-link" href="#navigation" aria-disabled="true">Previous</a></li>
                            <li class="page-item active"><a class="page-link" href="#navigation" aria-current="page">1</a></li>
                            <li class="page-item"><a class="page-link" href="#navigation">2</a></li>
                            <li class="page-item"><a class="page-link" href="#navigation">Next</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
        </section>

        <section class="mb-4" id="content" aria-labelledby="content-title">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h3 mb-0" id="content-title">Content</h2>
                <code>.card .table .list-group</code>
            </div>
            <div class="row g-3">
                <div class="col-xl-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-body fw-semibold">Card header</div>
                        <div class="card-body">
                            <h3 class="card-title h5">Card title</h3>
                            <p class="card-text text-body-secondary">Cards group related content and actions.</p>
                            <a class="btn btn-primary" href="#content">Primary action</a>
                        </div>
                        <div class="card-footer text-body-secondary">Card footer</div>
                    </div>
                </div>
                <div class="col-xl-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-body fw-semibold">List group</div>
                        <div class="list-group list-group-flush">
                            <a class="list-group-item list-group-item-action active" href="#content" aria-current="true">Active item</a>
                            <a class="list-group-item list-group-item-action" href="#content">Default item</a>
                            <a class="list-group-item list-group-item-action disabled" href="#content" aria-disabled="true">Disabled item</a>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="card border-0 shadow-sm overflow-hidden">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr><th>State</th><th>Description</th><th class="text-end">Value</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td><span class="badge text-bg-success">Ready</span></td><td>Default table row</td><td class="text-end">120</td></tr>
                                    <tr class="table-warning"><td><span class="badge text-bg-warning">Pending</span></td><td>Warning table row</td><td class="text-end">45</td></tr>
                                    <tr class="table-danger"><td><span class="badge text-bg-danger">Failed</span></td><td>Danger table row</td><td class="text-end">3</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="feedback" aria-labelledby="feedback-title">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <h2 class="h3 mb-0" id="feedback-title">Feedback and loading</h2>
                <code>.progress .spinner-*</code>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 p-lg-4">
                    <div class="progress mb-3" role="progressbar" aria-label="Primary progress" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar w-25">25%</div>
                    </div>
                    <div class="progress mb-4" role="progressbar" aria-label="Success progress" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                        <div class="progress-bar bg-success w-75">75%</div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-4 mb-4">
                        <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading</span></div>
                        <div class="spinner-grow text-success" role="status"><span class="visually-hidden">Loading</span></div>
                        <button class="btn btn-primary" type="button" disabled>
                            <span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>
                            Loading
                        </button>
                    </div>
                    <div class="placeholder-glow">
                        <span class="placeholder col-7"></span>
                        <span class="placeholder col-4"></span>
                        <span class="placeholder col-4"></span>
                        <span class="placeholder col-6"></span>
                    </div>
                </div>
            </div>
        </section>
    </main>
{/strip}
