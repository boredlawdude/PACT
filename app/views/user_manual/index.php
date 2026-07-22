<?php
declare(strict_types=1);
if (!function_exists('h')) {
    function h($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}
$appName = defined('APP_NAME') ? APP_NAME : 'Contracts App';
?>

<style>
  .manual-sidebar {
    position: sticky;
    top: 1rem;
    max-height: calc(100vh - 2rem);
    overflow-y: auto;
  }
  .manual-sidebar .nav-link {
    font-size: 0.85rem;
    padding: 0.2rem 0.75rem;
    color: #495057;
    border-left: 2px solid transparent;
  }
  .manual-sidebar .nav-link:hover,
  .manual-sidebar .nav-link.active {
    color: #0d6efd;
    border-left-color: #0d6efd;
    background: #f0f6ff;
  }
  .manual-sidebar .nav-link.section-header {
    font-weight: 600;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: .04em;
    color: #6c757d;
    margin-top: 0.75rem;
    pointer-events: none;
  }
  .manual-content h2 {
    border-bottom: 2px solid #dee2e6;
    padding-bottom: 0.4rem;
    margin-top: 2.5rem;
    margin-bottom: 1rem;
  }
  .manual-content h3 {
    margin-top: 1.5rem;
    color: #1e3a5f;
  }
  .manual-content section {
    scroll-margin-top: 80px;
  }
  .step-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 1.6rem;
    height: 1.6rem;
    border-radius: 50%;
    background: #1e3a5f;
    color: #fff;
    font-size: 0.78rem;
    font-weight: 700;
    flex-shrink: 0;
    margin-right: 0.5rem;
  }
  .tip-box {
    background: #eef6ff;
    border-left: 4px solid #0d6efd;
    padding: 0.75rem 1rem;
    border-radius: 0 0.375rem 0.375rem 0;
    margin: 1rem 0;
  }
  .warn-box {
    background: #fff8e1;
    border-left: 4px solid #ffc107;
    padding: 0.75rem 1rem;
    border-radius: 0 0.375rem 0.375rem 0;
    margin: 1rem 0;
  }
  .field-table th { background: #f8f9fa; font-size: 0.85rem; }
  .field-table td { font-size: 0.875rem; }
  .print-btn { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 1000; }
  @media print {
    .manual-sidebar, nav, .print-btn { display: none !important; }
    .manual-content { max-width: 100% !important; }
    a[href]:after { content: none !important; }
  }
</style>

<div class="container-fluid py-3">
  <div class="row">

    <!-- Sidebar TOC -->
    <div class="col-lg-2 d-none d-lg-block">
      <nav class="manual-sidebar" id="manualToc">
        <div class="nav flex-column">
          <span class="nav-link section-header">Getting Started</span>
          <a class="nav-link" href="#overview">Overview</a>
          <a class="nav-link" href="#login">Logging In</a>
          <a class="nav-link" href="#roles">Roles &amp; Permissions</a>
          <a class="nav-link" href="#navigation">Navigation</a>

          <span class="nav-link section-header">Core Workflows</span>
          <a class="nav-link" href="#dashboard">Dashboard</a>
          <a class="nav-link" href="#contracts-list">Contracts List</a>
          <a class="nav-link" href="#contracts-create">Creating a Contract</a>
          <a class="nav-link" href="#contracts-detail">Contract Detail</a>
          <a class="nav-link" href="#contracts-edit">Editing a Contract</a>
          <a class="nav-link" href="#contract-search">Searching &amp; Filtering</a>

          <span class="nav-link section-header">Contract Features</span>
          <a class="nav-link" href="#documents">Documents</a>
          <a class="nav-link" href="#change-orders">Change Orders</a>
          <a class="nav-link" href="#milestones">Milestones</a>
          <a class="nav-link" href="#approvals">Approval Workflow</a>
          <a class="nav-link" href="#docusign">DocuSign</a>
          <a class="nav-link" href="#history">History &amp; Notes</a>

          <span class="nav-link section-header">Other Sections</span>
          <a class="nav-link" href="#companies">Companies</a>
          <a class="nav-link" href="#people">People</a>
          <a class="nav-link" href="#intake">Contract Requests</a>
          <a class="nav-link" href="#dev-agreements">Dev Agreements</a>
          <a class="nav-link" href="#bidding">Bidding Compliance</a>
          <a class="nav-link" href="#procurement-gate">Procurement Gate</a>

          <span class="nav-link section-header">Administration</span>
          <a class="nav-link" href="#admin">System Settings</a>
          <a class="nav-link" href="#contract-types">Contract Types</a>
          <a class="nav-link" href="#statuses">Contract Statuses</a>
          <a class="nav-link" href="#milestone-types">Milestone Types</a>
          <a class="nav-link" href="#payment-terms">Payment Terms</a>
          <a class="nav-link" href="#departments">Departments</a>
          <a class="nav-link" href="#user-roles">User Roles</a>
          <a class="nav-link" href="#templates">Document Templates</a>
          <a class="nav-link" href="#merge-fields">Merge Fields</a>
          <a class="nav-link" href="#backup">Database Backup</a>
        </div>
      </nav>
    </div>

    <!-- Manual Content -->
    <div class="col-lg-10 manual-content">

      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <h1 class="h2 mb-1"><?= h($appName) ?> — User Manual</h1>
          <p class="text-muted mb-0">Complete guide for all users and administrators</p>
        </div>
        <a href="/index.php?page=dashboard" class="btn btn-outline-secondary btn-sm">← Back to App</a>
      </div>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="overview">
        <h2>Overview</h2>
        <p>
          PACT is a web-based contract lifecycle management system designed to handle
          the full workflow of town contracts — from contract creation, through execution and beyond.
          It provides a central, searchable database of all contracts, tracks counterparty vendors,
          automates approval routing, integrates with DocuSign for electronic signatures, and generates
          contract documents from standard templates.
        </p>
        <h2>When you use it & How you use it</h2>
        <p>
            The system is intended to be the single source for all contracts.  It will allow you to request legal review of a vendor contract or use of a town template contract (preferred method) based on fields entered by the user and/or uploaded documents, and then send that contract for electronic signature through DocuSign.  You can track key milestones such as town council or manager approval (when needed), manage change orders, and keep all related documents and notes organized in one place.  The system also provides a dashboard to see upcoming renewals and expirations, and a powerful search function to find contracts based on any criteria.  The system also logs all activity and changes for an audit trail.  The goal is to provide a comprehensive tool to manage the entire lifecycle of contracts in a way that is more efficient, organized, and compliant than using shared drives and email alone.
            When you create a contract in PACT, you are creating the official record of that agreement.  
            All key information about the contract should be entered and maintained in the system, and all documents should be uploaded or generated through it. 
             This ensures that everyone has access to the most up-to-date information and that important details don't get lost in email threads or shared drives.    
        
            NOTE:  prior to putting in a contract review or contract creatoion request, you should have already been approved by your Department head for the purchase at this point, selected the appropriate vendor, and have the necessary funds in your budget.  The system is not intended to replace the judgment and oversight of department heads or procurement staff, but rather to provide a tool to manage the information and workflow around contracts once those initial decisions have been made.  Therefore, YOU HAVE ALREADY COMPLIED WITH <a href="https://hollyspringsnc.sharepoint.com/sites/Intranet/Shared%20Documents/Forms/AllItems.aspx?id=%2Fsites%2FIntranet%2FShared%20Documents%2FPolicies%2FFN%2D12%20Purchasing%20Policy%2Epdf&parent=%2Fsites%2FIntranet%2FShared%20Documents%2FPolicies&p=true&ga=1" <a> THE TOWN'S PROCUREMENT POLICY </a> (linkable in intranet/Sharepoint) before you create a contract in the system.  If you have questions about procurement requirements, please contact a member of procurement in Finance.
            
        </p>
        <p>
                As an overview of the workflow, you will typically start by creating a new contract record in the system, entering all relevant information such as contract name, description, type, department, counterparty company, financial details, and so on.  You can then upload a draft contract document or generate one from a template.  Once the contract is ready for review, you will submit it into the approval workflow.  Depending on the contract type and value, it may require approvals from your manager, procurement, legal, risk management, and/or town council.  The system will route the contract to the appropriate approvers based on configurable rules.  Approvers can log in to review the contract details and documents, and stamp their approval when ready.  Once all approvals are obtained, you can send the contract for electronic signature via DocuSign directly from the system.  After execution, you can track key milestones, manage change orders if needed, and keep all related documents and notes organized in one place.
        </p>       
             <h3>What You Can Do</h3>
        <ul>
          <li>Create and manage contracts with all financial, party, and date information</li>
          <li>Route contracts through configurable approval workflows (Manager, Purchasing, Legal, Risk Manager, Town Council)</li>
          <li>Upload, compare, and generate documents — including Word (.docx) and HTML formats</li>
          <li>Allows the Contract Administrator to send contracts for electronic signature via DocuSign without that annoying process of inserting signatures in the correct place</li>
          <li>Track change orders, key milestones, and bidding compliance</li>
          <li>Manage a vendor/company database linked to contracts, and find all contracts with specific vendor</li>
          <li>Process external contract requests (intake) and Development Agreement submissions</li>
          <li>Administer users, roles, departments, and system settings</li>
        </ul>
      </section>     
            <h2>    There will be training on this, but the basic steps are: </h2>
        <p>
                <ul>
                    <li>Create a new contract record and fill in all relevant information.  This can be done through the "New Contract" Tab directly in the system, or the <a href="https://pact.schifano.com/contract_intake.php" <a> "public view form"</a></li>
                    <li> The "Contract Name" Should be something intuitive such as "Landscaping Contract for Town Hall 2026" or "Annual Software Subscription for XYZ Vendor". </li>
                    <li> The description can be a brief summary of the scope or purpose of the contract. When using the Town Standard contracts (hopefully 99.9% of the time) it will reference "Exhibit A" So it might be "Town Hall Landscaping contract according to the terms and conditions on Exhibit A" would be the standard format for this field </li>
                    <li>Select the appropriate contract type from the dropdown (this may determine which template is used for document generation).</li>  
                    <li>Set the status to "Draft" to indicate that it's a work in progress. </li>
                    <li> If this contract uses the town's standard template and doesn't require legal review, check the "Use Standard Contract" box. </li>
                    <li> If the vendor carries insurance with a COI of $5 Million or more, check that box to potentially waive Risk Manager approval.</li>
                    <li>Select "Payment Type" - This is important! We often gloss over what type of contract this will be- whether we are paying for work under a "lump sum" meaning, for $100k a vendor will do everything in a Scope of Work (ie, Exhibit A) no matter what the time or what the expense, or are we paying based on "Time and Materials" meaning we are paying them an hourly rate to do work over a period of time.  If you are confused, ask Legal or Procurement  </li>
                    <li>Upload relevant docs such as COI and the "Scope of Work" which will be the Exhibit A to the town's standard contract template. </li>
                    <li>SAVE CHANGES!  The system will route it to the appropriate approvers based on the contract type and value.  You can track the approval status in the system and send reminders if needed.</li>
                    <li>Approvers (Manager, Procurement, Legal, Risk Manager) will see what they need to review in their dashboard.</li>
        
                </ul>
        

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="login">
        <h2>Logging In</h2>
        <p>Navigate to the application URL in your browser. You will be redirected to the login page if you are not already authenticated.  If you cannot login, contact <a href="mailto:john.schifano@hollyspringsnc.gov">John Schifano (for now)</a>.</p>
        <ol>
          <li>Enter your <strong>email address</strong> and <strong>password</strong>.</li>
          <li>Click <strong>Log In</strong>. You will land on the Dashboard.</li>
        </ol>
        <div class="tip-box">
          <strong>Forgot your password?</strong> Contact a system administrator to reset it. Admins can use the <strong>Admin Reset</strong> button in the top navigation bar.
        </div>
        <p>To log out, click <strong>Logout</strong> in the navigation bar at any time.</p>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="roles">
        <h2>Roles &amp; Permissions</h2>
        <p>Access to features is controlled by roles assigned to your account. You may have one or more roles, and some roles may be scoped to a specific department.</p>
        <div class="table-responsive">
          <table class="table table-bordered field-table">
            <thead>
              <tr>
                <th>Role</th>
                <th>What You Can Do</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><strong>SUPERUSER / ADMIN</strong></td>
                <td>Full access to everything — all contracts, all settings, all users. Can stamp approvals on any contract, reset passwords, and configure the system.</td>
              </tr>
              <tr>
                <td><strong>DEPT_CONTRACT_ADMIN</strong></td>
                <td>Manage contracts within your assigned department. Create, edit, and manage documents for department contracts. Cannot access system-wide settings.</td>
              </tr>
              <tr>
                <td><strong>TOWN_MANAGER</strong></td>
                <td>Can stamp the <em>Manager</em> approval on contracts that require it.</td>
              </tr>
              <tr>
                <td><strong>PROCUREMENT</strong></td>
                <td>Can stamp the <em>Purchasing</em> approval on contracts that require it.</td>
              </tr>
              <tr>
                <td><strong>LEGAL_ADMIN</strong></td>
                <td>Can stamp the <em>Legal</em> approval on contracts that require it. (Hint:  If you use the town's standard contracts that are pre-approved, this role may not be needed for every contract & the process is speeded up!)</td>
              </tr>
              <tr>
                <td><strong>RISK_MANAGER</strong></td>
                <td>Can stamp the <em>Risk Manager</em> approval on contracts that require it.  The Town's standard requirement is to have a COI for at least $5 Million for any vendor that does work on Town property or work on Town Equipment.  If a vendor does not have this amount of coverage, approval from the Risk Manager will be necessary.</td>
              </tr>
              <tr>
                <td><strong>TOWN_COUNCIL</strong></td>
                <td>Can stamp the <em>Town Council</em> approval on contracts that require it.</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="tip-box">Your current roles are listed on the Dashboard, below your name in the "Your Access" card.</div>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="navigation">
        <h2>Navigation</h2>
        <p>The top navigation bar is always visible. Key links:</p>
        <ul>
          <li><strong>Dashboard</strong> — Your home screen with alerts and summary</li>
          <li><strong>Contracts</strong> — Full contract list with search/filter</li>
          <li><strong>New Contract</strong> — Start creating a contract</li>
          <li><strong>Contract Requests</strong> — Review externally submitted contract requests <em>(yellow badge = pending items)</em></li>
          <li><strong>Dev Agreements</strong> — Manage Development Agreements</li>
          <li><strong>Intake Submissions</strong> — Review DA public submissions <em>(yellow badge = pending)</em></li>
          <li><strong>Companies</strong> — Vendor/counterparty database</li>
          <li><strong>New Company</strong> — Add a vendor record</li>
          <li><strong>People</strong> — Internal user directory</li>
          <li><strong>Departments</strong> — Department list</li>
          <li><strong>New User</strong> — Create a user account <em>(Admin only)</em></li>
          <li><strong>System Settings</strong> — All admin configuration <em>(Admin only)</em></li>
          <li><strong>User Manual</strong> — This page</li>
          <li><strong>Logout</strong> — End your session</li>
        </ul>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="dashboard">
        <h2>Dashboard</h2>
        <p>After logging in you will land on the Dashboard, which gives you a personalized summary of the system.</p>
        <h3>Alert Banners</h3>
        <p>Colored banners at the top warn you about contracts that may need attention:</p>
        <ul>
          <li><strong>Stale Drafts</strong> — Contracts in Draft status for more than 5 days</li>
          <li><strong>Pending Execution</strong> — Contracts out for signature but not yet executed</li>
          <li><strong>Pending Reviews</strong> — Contracts waiting in review statuses</li>
          <li><strong>Town Council</strong> — Contracts in Town Council status</li>
          <li><strong>Approval Needed</strong> — Contracts where your approval role is required but not yet stamped</li>
        </ul>
        <h3>My Pending Approvals</h3>
        <p>If you hold an approval role, you will see a row of quick-filter links such as "Manager (3)" — click one to jump to the filtered contracts list showing only contracts awaiting that approval type.</p>
        <h3>Contracts Table</h3>
        <p>The main table lists all contracts you have access to. You can:</p>
        <ul>
          <li>Filter by status using the buttons above the table</li>
          <li>Click a contract name to open its detail view</li>
          <li>Select multiple rows and use <strong>Bulk Delete</strong> to remove drafts</li>
        </ul>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="contracts-list">
        <h2>Contracts List</h2>
        <p>Go to <strong>Contracts</strong> in the nav to see all contracts. The list is sortable and supports advanced filtering (see <a href="#contract-search">Searching &amp; Filtering</a>).</p>
        <p>Each row shows: Contract Number, Name, Vendor, Department, Status, Start/End Dates, and Total Value. Click any row to open the full detail view.</p>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="contracts-create">
        <h2>Creating a Contract</h2>
        <p>Click <strong>New Contract</strong> in the navigation bar, or use the <strong>+ New Contract</strong> button on the contracts list page.</p>

        <h3>Step 1 — Basic Information</h3>
        <div class="table-responsive">
          <table class="table table-bordered field-table">
            <thead><tr><th>Field</th><th>Description</th><th>Required?</th></tr></thead>
            <tbody>
              <tr><td>Contract Name</td><td>Descriptive title for the contract (e.g., "Lawn Maintenance Services 2026")</td><td>Yes</td></tr>
              <tr><td>Description</td><td>Free-text summary of scope or purpose</td><td>No</td></tr>
              <tr><td>Contract Type</td><td>Select the type from the admin-defined list (drives which template is used for document generation)</td><td>Yes</td></tr>
              <tr><td>Status</td><td>Current workflow stage (e.g., Draft, Legal Review, Executed)</td><td>Yes</td></tr>
              <tr><td>Status Comment</td><td>Optional note about the current status</td><td>No</td></tr>
              <tr><td>Use Standard Contract</td><td>Check if this uses the standard contract form (may waive certain approvals)</td><td>No</td></tr>
              <tr><td>Minimum Insurance COI ≥ $5M</td><td>Check if the vendor carries high-value insurance (may waive Risk Manager approval)</td><td>No</td></tr>
            </tbody>
          </table>
        </div>

        <h3>Step 2 — Department &amp; Responsible Person</h3>
        <div class="table-responsive">
          <table class="table table-bordered field-table">
            <thead><tr><th>Field</th><th>Description</th><th>Required?</th></tr></thead>
            <tbody>
              <tr><td>Department</td><td>The town department this contract belongs to. Affects who can administer it and is used in the auto-generated contract number.</td><td>Yes</td></tr>
              <tr><td>Responsible Employee</td><td>The internal staff member who owns this contract</td><td>No</td></tr>
            </tbody>
          </table>
        </div>

        <h3>Step 3 — Financial &amp; Terms</h3>
        <div class="table-responsive">
          <table class="table table-bordered field-table">
            <thead><tr><th>Field</th><th>Description</th><th>Required?</th></tr></thead>
            <tbody>
              <tr><td>Total Contract Value</td><td>Dollar value of the contract (used for approval thresholds)</td><td>No</td></tr>
              <tr><td>PO Number / PO Amount</td><td>Associated purchase order details</td><td>No</td></tr>
              <tr><td>Account Number</td><td>General ledger account for billing</td><td>No</td></tr>
              <tr><td>Payment Terms</td><td>Select from admin-defined payment types (e.g., Net 30)</td><td>No</td></tr>
              <tr><td>Governing Law</td><td>Jurisdiction for the contract (e.g., "State of North Carolina")</td><td>No</td></tr>
              <tr><td>Start Date / End Date</td><td>Contract performance period</td><td>No</td></tr>
              <tr><td>Auto-Renew</td><td>Check if contract automatically renews</td><td>No</td></tr>
              <tr><td>Renewal Term (months)</td><td>If auto-renew, how many months per term</td><td>No</td></tr>
            </tbody>
          </table>
        </div>

        <h3>Step 4 — Vendor</h3>
        <div class="table-responsive">
          <table class="table table-bordered field-table">
            <thead><tr><th>Field</th><th>Description</th><th>Required?</th></tr></thead>
            <tbody>
              <tr><td>Vendor Company Name</td><td>Select the vendor/company from the database. Once selected, address and contact fields may auto-populate.</td><td>No</td></tr>
              <tr><td>Primary Contact</td><td>Main contact person at the vendor for this contract (May or May not be the person who signs for the vendor)</td><td>No</td></tr>
              <tr><td>Signer 1–3 (Name/Title/Email)</td><td>People authorized to sign on the vendor side (used for DocuSign)</td><td>No</td></tr>
              <tr><td>Tax ID</td><td>Vendor federal tax ID / EIN</td><td>No</td></tr>
              <tr><td>COI Expiration Date</td><td>When their certificate of insurance expires</td><td>No</td></tr>
            </tbody>
          </table>
        </div>

        <div class="tip-box">
          <strong>Contract Number</strong> is auto-generated after saving, using the format <code>YY-DEPTINIT-XXX_YYY-SEQ</code> (e.g., <code>26-PLAN-LAW_MAI-42</code>). You do not need to enter it manually.
        </div>

        <p>Click <strong>Save Contract</strong> when done. You will be taken to the contract detail view.</p>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="contracts-detail">
        <h2>Contract Detail View</h2>
        <p>Click a contract's name from any list to open its full detail page. This page is the hub for all contract activity. It is organized into expandable cards:</p>

        <h3>Summary Card</h3>
        <p>Shows department, vendor company, contract type, primary contact, and key flags (Standard Contract, COI ≥$5M). Status and status comment appear at the top of the page.</p>

        <h3>Financial &amp; Terms Card</h3>
        <p>Shows total value, PO info, account numbers, payment terms, governing law, start/end dates, and renewal settings.</p>

        <h3>Vendor Details Card</h3>
        <p>Shows full vendor address, all signer information, email, phone, website, tax ID, and COI expiration date.</p>

        <h3>Change Orders Card</h3>
        <p>Lists all change orders with amounts and approval dates. Use <strong>+ Add Change Order</strong> to record a modification. See <a href="#change-orders">Change Orders</a>.</p>

        <h3>Contract Milestones Card</h3>
        <p>Tracks key dates in the contract lifecycle. Use the quick-add form at the bottom to record a milestone. See <a href="#milestones">Milestones</a>.</p>

        <h3>Approvals Card</h3>
        <p>Shows the required approvals for this contract (based on rules) and the date each was stamped. Approval holders can stamp their own approval here. See <a href="#approvals">Approval Workflow</a>.</p>

        <h3>Documents Card</h3>
        <p>Lists all uploaded and generated documents (exhibits, drafts, final versions). Use buttons to upload, generate, compare, or merge documents. See <a href="#documents">Documents</a>.</p>

        <h3>DocuSign Card</h3>
        <p>Shows the current DocuSign envelope status (if one has been sent). See <a href="#docusign">DocuSign</a>.</p>

        <h3>History &amp; Notes Card</h3>
        <p>Displays a full audit trail of status changes, document generation events, approval stamps, and manual notes. You can add a text note at any time using the note form.</p>

        <h3>Page Buttons</h3>
        <p>At the top of the page:</p>
        <ul>
          <li><strong>Change Contract Info</strong> — Opens the edit form</li>
          <li><strong>Generate HTML</strong> — Generates an HTML document from the contract's type template and opens it in a new tab</li>
          <li><strong>Generate Word Doc</strong> — Generates a .docx document and saves it to the contract's document list</li>
          <li><strong>Generate Selected Doc</strong> — Choose a specific contract type template to generate from a dropdown</li>
        </ul>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="contracts-edit">
        <h2>Editing a Contract</h2>
        <p>From the contract detail page, click <strong>Change Contract Info</strong>. All fields from the create form are available for editing. Make your changes and click <strong>Update Contract</strong>.</p>
        <div class="warn-box">
          <strong>Changing the contract type</strong> will change which template is used for future document generation. It will not affect already-generated documents.
        </div>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="contract-search">
        <h2>Searching &amp; Filtering Contracts</h2>
        <p>From the Contracts list page, use the search/filter bar at the top:</p>
        <ul>
          <li><strong>Search box</strong> — Searches contract number, name, description, and vendor company name simultaneously</li>
          <li><strong>Status</strong> — Filter to a specific workflow status</li>
          <li><strong>Department</strong> — Filter by department</li>
          <li><strong>Responsible Person</strong> — Filter by the assigned employee</li>
          <li><strong>End Date From / To</strong> — Filter contracts by end date range</li>
          <li><strong>Company</strong> — Filter contracts associated with a specific vendor</li>
        </ul>
        <p>Click <strong>Search</strong> to apply filters, or <strong>Clear</strong> to reset. Results update the contract table instantly.</p>
        <div class="tip-box">On the Dashboard, you can also click the <strong>pending approval</strong> quick-filter links (e.g., "Manager (3)") to jump straight to contracts awaiting that approval type.</div>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="documents">
        <h2>Documents</h2>
        <p>All contract documents (uploaded files and generated drafts) are managed in the Documents section at the bottom of the contract detail page.</p>

        <h3>Uploading a Document</h3>
        <ol>
          <li>Click <strong>Upload Document</strong> on the contract detail page.</li>
          <li>Choose a file, set the document type, and optionally assign an <strong>Exhibit Label</strong> (e.g., "Exhibit A").</li>
          <li>Click <strong>Upload</strong>. The file appears in the documents list.</li>
        </ol>

        <h3>Generating a Document</h3>
        <ol>
          <li>Click <strong>Generate Word Doc</strong> (or <strong>Generate HTML</strong>) at the top of the contract detail page.</li>
          <li>The system merges contract data into the template and saves the file as a new document entry.</li>
          <li>The generated draft appears in the Documents list for download.</li>
        </ol>
        <div class="tip-box">Use <strong>Generate Selected Doc</strong> to pick a different contract type template for one-off generation without changing the contract's assigned type.</div>

        <h3>Comparing Documents</h3>
        <p>Click <strong>Compare Documents</strong> to open a side-by-side diff of any two documents on the contract. Useful for reviewing changes between draft versions.</p>

        <h3>Merging to PDF</h3>
        <p>Click <strong>Merge as PDF</strong> to combine multiple selected documents into a single PDF file.</p>

        <h3>Exhibit Labels</h3>
        <p>Assigning an exhibit label (Exhibit A, Exhibit B, etc.) to a document causes it to appear in the <code>{{exhibit_list}}</code> merge field in generated documents, formatted as "Exhibit A - [Description]".</p>

        <h3>Emailing a Document</h3>
        <p>From the Documents list, each file has an <strong>Email</strong> option. This opens a form to compose and send the document as an email attachment directly from the app.</p>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="change-orders">
        <h2>Change Orders</h2>
        <p>Change orders record modifications to a contract's scope, cost, or timeline after execution.</p>

        <h3>Adding a Change Order</h3>
        <ol>
          <li>Open the contract detail page and scroll to the <strong>Change Orders</strong> card.</li>
          <li>Click <strong>+ Add Change Order</strong>.</li>
          <li>Fill in the fields:
            <ul>
              <li><strong>Change Order Number</strong> — Sequential identifier (e.g., CO-001)</li>
              <li><strong>Amount</strong> — Dollar value of the change (positive or negative)</li>
              <li><strong>Approval Date</strong> — When the change order was authorized</li>
              <li><strong>Justification</strong> — Description of why the change was needed</li>
            </ul>
          </li>
          <li>Click <strong>Save</strong>.</li>
        </ol>

        <h3>Generating a Change Order Document</h3>
        <p>From the Change Orders table on the contract detail page, click <strong>Generate Doc</strong> next to any change order to produce a formatted .docx file.</p>

        <h3>Editing / Deleting</h3>
        <p>Use the <strong>Edit</strong> and <strong>Delete</strong> buttons in the change orders table. Deletion is permanent and requires confirmation.</p>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="milestones">
        <h2>Contract Milestones</h2>
        <p>Milestones let you record key dates and events in a contract's life — such as "Notice to Proceed," "Work Started," or "Final Inspection."</p>

        <h3>Adding a Milestone</h3>
        <ol>
          <li>Open the contract detail page and scroll to the <strong>Contract Milestones</strong> card.</li>
          <li>Use the quick-add form at the bottom of the card:
            <ul>
              <li><strong>Milestone Type</strong> — Select from the admin-defined list (e.g., "Notice to Proceed")</li>
              <li><strong>Date</strong> — The date the milestone occurred or is expected</li>
              <li><strong>Notes</strong> — Optional free-text details (up to 500 characters)</li>
            </ul>
          </li>
          <li>Click <strong>+ Add Milestone</strong>.</li>
        </ol>
        <p>Existing milestones are listed in the table above the form, sorted by date. The name of the person who recorded each entry is shown.</p>
        <p>To remove a milestone, click the <strong>Delete</strong> button in the table row.</p>

        <div class="tip-box">
          <strong>Admins:</strong> Milestone types are configured under <strong>System Settings → Milestone Types</strong>. Add types like "Work Started," "Notice to Proceed," or "Final Payment Due" there before users can select them.
        </div>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="approvals">
        <h2>Approval Workflow</h2>
        <p>
          The system can require up to five approval stamps before a contract is sent for signature:
          <strong>Manager, Purchasing, Legal, Risk Manager,</strong> and <strong>Town Council</strong>.
          Which approvals are required is determined by configurable <strong>Approval Rules</strong>.
        </p>

        <h3>How Rules Work</h3>
        <p>Each approval rule defines a condition on a contract field (e.g., "Total Value &gt; $30,000") and specifies which approval type is required when that condition is true. Rules can also be waived if the contract uses the standard form or carries high-value insurance.</p>

        <h3>Viewing Required Approvals</h3>
        <p>On the contract detail page, scroll to the <strong>Approvals</strong> card. It shows a table with each potential approval type, whether it is required for this contract, and the date it was stamped (if any).</p>

        <h3>Stamping an Approval</h3>
        <ol>
          <li>Open the contract detail page.</li>
          <li>Scroll to the <strong>Approvals</strong> card.</li>
          <li>In the row for your approval type (e.g., "Manager Approval"), click <strong>Stamp Today</strong>.</li>
          <li>A confirmation dialog appears. Confirm to record today's date as the approval date.</li>
        </ol>
        <div class="warn-box">
          <strong>Note:</strong> Only users with the matching approval role (or an Admin/Superuser) can stamp an approval. If the required role is not assigned to any user, an Admin can still override and stamp.
        </div>

        <h3>Override Approvals (Admin)</h3>
        <p>If a contract does not meet an approval rule's conditions but you still want to require that approval, click the dash (<strong>—</strong>) in the "Required?" column to add an override requirement.</p>

        <h3>Approval Override for Signature (Admin)</h3>
        <p>When sending to DocuSign, if required approvals are not yet complete, a warning modal appears. Admins can choose to override and send anyway — this is logged in the contract history.</p>

        <h3>Email Risk Manager</h3>
        <p>On contracts where Risk Manager approval is required but not yet stamped, an <strong>Email Risk Manager</strong> button appears. This sends a pre-composed notification email to all users with the RISK_MANAGER role.</p>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="docusign">
        <h2>DocuSign (Electronic Signatures)</h2>
        <p>The system integrates with DocuSign to send contract documents for electronic signature and track their progress.</p>

        <h3>Prerequisites</h3>
        <ul>
          <li>A system administrator must complete the DocuSign OAuth setup in System Settings.</li>
          <li>The contract must have at least one document uploaded/generated.</li>
          <li>Signer information (name and email) must be on the contract or entered at send time.</li>
        </ul>

        <h3>Sending for Signature</h3>
        <ol>
          <li>From the contract detail page, click <strong>Send via DocuSign</strong> (in the DocuSign card or top buttons).</li>
          <li>Select the document to send.</li>
          <li>Configure signers — name, email, and signing order. Town-side and vendor signers can both be added.</li>
          <li>Optionally customize the email subject and message.</li>
          <li>Click <strong>Send Envelope</strong>. The envelope is created in DocuSign and signers receive email invitations.</li>
        </ol>

        <h3>Tracking Status</h3>
        <p>The DocuSign card on the contract detail page shows the current envelope status (e.g., "Sent," "Completed," "Voided"). Once all parties sign, the status updates to "Completed."</p>

        <h3>Voiding an Envelope</h3>
        <p>If a contract needs to be re-sent or recalled, click <strong>Void Envelope</strong> in the DocuSign card. Provide a reason for voiding, then send a new envelope when ready.</p>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="history">
        <h2>History &amp; Notes</h2>
        <p>Every contract has a full audit trail in the <strong>History</strong> card at the bottom of the detail page. Events logged automatically include:</p>
        <ul>
          <li>Status changes (who changed it, from/to)</li>
          <li>Document generation and deletion</li>
          <li>Approval stamps and bypasses</li>
          <li>DocuSign envelope activity</li>
          <li>Bulk re-apply of approvals and undo actions</li>
        </ul>
        <h3>Adding a Manual Note</h3>
        <p>Use the note form in the History card to add free-text notes — useful for recording phone conversations, decisions, or reminders. Notes are timestamped with your name. Admins can delete individual notes if needed.</p>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="companies">
        <h2>Companies (Vendors)</h2>
        <p>The Companies section is a searchable database of all vendors, contractors, and counterparties. A company record must exist before it can be linked to a contract.</p>

        <h3>Adding a Company</h3>
        <ol>
          <li>Click <strong>New Company</strong> in the navigation bar.</li>
          <li>Fill in the company details:
            <ul>
              <li><strong>Name</strong> — Legal business name (required)</li>
              <li><strong>Vendor ID</strong>, <strong>Tax ID / EIN</strong></li>
              <li><strong>Address</strong> fields (line 1, city, state, zip, country)</li>
              <li><strong>Contact</strong> — Primary contact name, phone, email</li>
              <li><strong>Signers</strong> — Up to 3 authorized signers (name, title, email). These pre-populate when you link this company to a contract.</li>
              <li><strong>COI Information</strong> — Insurance carrier name, expiration date, verified by</li>
              <li><strong>NC Secretary of State ID</strong> — Use the link to go to the NC Secretary of State's wesite to ensure the Vendor name is correct and they are registered to do business in NC.  Put in the SOSID number in th</li>
            </ul>
          </li>
          <li>Click <strong>Save Company</strong>.</li>
        </ol>

        <h3>Company Detail View</h3>
        <p>Click a company name to open its detail page. This shows all company information plus:</p>
        <ul>
          <li><strong>Linked Contracts</strong> — All contracts where this company is the vendor</li>
          <li><strong>Linked People</strong> — Internal contacts associated with the company</li>
          <li>A <strong>COI expiration warning</strong> if the certificate is expired or expiring soon</li>
        </ul>

        <h3>Importing Companies from PDF</h3>
        <p>Use the <strong>Import from PDF</strong> option (on the Companies list page) to bulk-import vendor data from a PDF vendor roster. Review the extracted data before confirming the import.</p>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="people">
        <h2>People (Users)</h2>
        <p>The People section manages all user accounts. Only Admins and Superusers can create or edit user records.</p>

        <h3>Creating a User</h3>
        <ol>
          <li>Click <strong>New User</strong> in the nav (Admin only) or go to <strong>System Settings → Manage Users</strong>.</li>
          <li>Fill in: First name, last name, email (used as login), department, and whether they can log in and are a town employee.</li>
          <li>Set an initial password.</li>
          <li>Save the user, then assign roles on their profile page.</li>
        </ol>

        <h3>Assigning Roles</h3>
        <p>On a person's edit/detail page, use the Roles section to assign roles. Roles can be global (apply everywhere) or department-scoped (apply only within a specific department).</p>

        <h3>Password Reset</h3>
        <p>Admins can set a new password for any user from their profile page. For the admin account itself, use the <strong>Admin Reset</strong> button in the top navigation bar.</p>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="intake">
        <h2>Contract Requests (Intake)</h2>
        <p>External users (e.g., department staff who do not log in) can submit a contract request through a public intake form at <code>/contract_intake.php</code>. Submitted requests appear in the <strong>Contract Requests</strong> list with a yellow pending badge.</p>

        <h3>Reviewing a Request</h3>
        <ol>
          <li>Click <strong>Contract Requests</strong> in the nav.</li>
          <li>Click a submission to view its details (name, type, estimated value, vendor, submitter info).</li>
          <li>Choose an action:
            <ul>
              <li><strong>Import</strong> — Creates a new contract pre-populated with the submission data. The submission is marked as imported and linked to the new contract.</li>
              <li><strong>Reject</strong> — Marks the submission as rejected. Optionally provide a reason.</li>
            </ul>
          </li>
        </ol>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="dev-agreements">
        <h2>Development Agreements</h2>
        <p>Development Agreements are a specialized contract type for land development projects. They have all standard contract fields plus extensive DA-specific property and developer information.</p>

        <h3>DA-Specific Fields</h3>
        <ul>
          <li><strong>Property/Tracts</strong> — PIN, real estate ID, address, acreage, and owner for each parcel involved</li>
          <li><strong>Developer Entity</strong> — Corporation name, entity type, state of incorporation, contact, address</li>
          <li><strong>Parties</strong> — Property owner, applicant, attorney</li>
          <li><strong>Key Dates</strong> — Planning Board hearing date, Town Council hearing date, anticipated start/end</li>
          <li><strong>Project Details</strong> — Description, proposed improvements, current/proposed zoning, comp plan designation</li>
          <li><strong>Special Flags</strong> — Parkland dedication, transportation tier, unit count, daily flow maximum (gpd)</li>
        </ul>

        <h3>DA Intake Workflow</h3>
        <p>Developers can submit a DA request through the public intake form at <code>/dev_agreement_intake.php</code>. Submissions appear under <strong>Intake Submissions</strong> in the nav. Admins can import them to create a full DA contract or reject them.</p>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="bidding">
        <h2>Bidding Compliance</h2>
        <p>The Bidding Compliance section on a contract allows you to record events and outcomes from the competitive bidding process.</p>
        <h3>Fields</h3>
        <ul>
          <li><strong>Event Date</strong> — Date of the bidding event</li>
          <li><strong>Compliance Status</strong> — Was the bid process compliant?</li>
          <li><strong>Number of Bids Received</strong></li>
          <li><strong>Lowest Bid Amount</strong></li>
          <li><strong>Recommendation</strong> — Staff recommendation based on bidding</li>
          <li><strong>Notes</strong> — Additional details</li>
        </ul>
        <p>Records appear on the contract detail page. Use the <strong>Delete</strong> button to remove a record if entered in error.</p>
      </section>

     

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="admin">
        <h2>System Settings (Admin)</h2>
        <p>Go to <strong>System Settings</strong> in the navigation bar (Admin/Superuser only). This is the central hub for all configuration.</p>
        <p>The <strong>Admin Tools</strong> panel provides quick buttons to all admin sections:</p>
        <ul>
          <li><a href="#statuses">Contract Statuses</a></li>
          <li><a href="#payment-terms">Payment Types</a></li>
          <li><a href="#user-roles">User Roles</a></li>
          <li><a href="#contract-types">Contract Types</a></li>
          <li>Approval Rules</li>
          <li><a href="#milestone-types">Milestone Types</a></li>
          <li>Manage Users</li>
          <li><a href="#departments">Departments</a></li>
          <li>Contract Bulk Import</li>
          <li><a href="#merge-fields">Merge Field Reference</a></li>
          <li><a href="#backup">Database Backup</a></li>
        </ul>

        <h3>Storage &amp; Template Paths</h3>
        <p>In the settings form, configure:</p>
        <ul>
          <li><strong>Storage Base Directory</strong> — Server path where uploaded and generated files are saved</li>
          <li><strong>DOCX Template Directory</strong> — Path to Word template files</li>
          <li><strong>HTML Template Directory</strong> — Path to HTML template files</li>
          <li><strong>Default Email Message</strong> — Template text for document email sends (supports <code>{contract_number}</code>, <code>{contract_name}</code>, <code>{sender_name}</code> placeholders)</li>
        </ul>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="contract-types">
        <h2>Contract Types (Admin)</h2>
        <p>Contract types define which document templates are available for a contract. Go to <strong>System Settings → Contract Types</strong>.</p>
        <h3>Fields</h3>
        <ul>
          <li><strong>Name</strong> — Display name (e.g., "Professional Services Agreement")</li>
          <li><strong>Description</strong> — Internal note about when to use this type</li>
          <li><strong>Formal Bidding Required</strong> — Flag for contracts that always require a bid process</li>
          <li><strong>HTML Template File</strong> — Upload the HTML template for this type</li>
          <li><strong>DOCX Template File</strong> — Upload the Word template for this type</li>
        </ul>
        <p>See <a href="#templates">Document Templates</a> and <a href="#merge-fields">Merge Fields</a> for template formatting instructions.</p>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="statuses">
        <h2>Contract Statuses (Admin)</h2>
        <p>Go to <strong>System Settings → Contract Statuses</strong> to manage the list of workflow statuses available when editing a contract.</p>
        <p>Use the inline table to add new statuses, edit existing names, or delete statuses that are no longer needed. You cannot delete a status that is currently assigned to a contract.</p>
        <div class="tip-box">The Dashboard and search filter automatically recognize key status names (Draft, Executed, Out for Signature, etc.) for grouping and color-coding. Keep these standard names unchanged for full functionality.</div>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="milestone-types">
        <h2>Milestone Types (Admin)</h2>
        <p>Go to <strong>System Settings → Milestone Types</strong> to define the types of milestones that users can record on contracts.</p>
        <p>Examples: <em>Notice to Proceed, Work Started, Substantial Completion, Final Inspection, Final Payment Due, Contract Extension, Renewal Executed.</em></p>
        <h3>Managing Types</h3>
        <ul>
          <li>Use the <strong>+ Add</strong> row at the bottom of the table to create a new type</li>
          <li>Edit the name or sort order inline and click <strong>Save</strong></li>
          <li>Click <strong>Delete</strong> to remove a type — this is blocked if the type is used on any contract</li>
          <li><strong>Sort Order</strong> controls the order types appear in the dropdown (lower number = higher in list)</li>
        </ul>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="payment-terms">
        <h2>Payment Terms (Admin)</h2>
        <p>Go to <strong>System Settings → Payment Types</strong> to manage the dropdown options for contract payment terms (e.g., Net 30, Net 60, Due Upon Receipt).</p>
        <p>Each term has a name, optional description, sort order, and active flag. Inactive terms are hidden from new contracts but remain on existing ones.</p>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="departments">
        <h2>Departments (Admin)</h2>
        <p>Go to <strong>Departments</strong> in the nav (or <strong>System Settings → Departments</strong>) to manage the list of town departments.</p>
        <h3>Fields</h3>
        <ul>
          <li><strong>Department Name</strong> — Full name (e.g., "Planning &amp; Zoning")</li>
          <li><strong>Department Code</strong> — Short code (e.g., "PLAN")</li>
          <li><strong>Department Initials</strong> — 2–4 letters used in the auto-generated contract number (e.g., "PLN")</li>
        </ul>
        <div class="warn-box">Changing a department's initials will affect the format of future contract numbers for that department. Existing contract numbers are not changed.</div>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="user-roles">
        <h2>User Roles (Admin)</h2>
        <p>Go to <strong>System Settings → User Roles</strong> to manage the role definitions. You can create custom roles, edit names and descriptions, and toggle roles active/inactive.</p>
        <p>Roles are assigned to individual users on their People profile page. A role can be assigned globally or scoped to a specific department.</p>
        <div class="warn-box">Do not delete or rename the standard role keys (ADMIN, SUPERUSER, PROCUREMENT, etc.) — the system uses these keys internally for permission checks. You can change the display name and description freely.</div>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="templates">
        <h2>Document Templates</h2>
        <p>Contract documents are generated by merging contract data into a template file (HTML or DOCX). Templates are uploaded via <strong>System Settings → Contract Types</strong> by editing a contract type and uploading a file.</p>

        <h3>HTML Templates</h3>
        <p>Write a standard HTML file. Use double-brace merge fields anywhere you want contract data inserted: <code>{{field_name}}</code></p>
        <p>Example: <code>This agreement is entered into by {{counterparty_company_name}} on {{start_date}}.</code></p>

        <h3>Word (.docx) Templates</h3>
        <p>Create a standard .docx file in Word. Use single-brace merge fields: <code>${field_name}</code></p>
        <p>The system uses PHPWord to process these templates.</p>

        <h3>Modifiers</h3>
        <p>Append <code>|upper</code> to any field to output it in all uppercase:</p>
        <p>HTML: <code>{{counterparty_company_name|upper}}</code> → <code>ACME CONSTRUCTION LLC</code></p>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="merge-fields">
        <h2>Merge Fields Reference</h2>
        <p>The following merge fields are available in contract document templates. Go to <strong>System Settings → Merge Field Reference</strong> for the full in-app list.</p>
        <div class="table-responsive">
          <table class="table table-bordered table-sm field-table">
            <thead><tr><th>Field Name</th><th>Contents</th></tr></thead>
            <tbody>
              <tr><td><code>name</code></td><td>Contract name</td></tr>
              <tr><td><code>contract_number</code></td><td>Auto-generated contract number</td></tr>
              <tr><td><code>description</code></td><td>Contract description</td></tr>
              <tr><td><code>total_contract_value</code></td><td>Value formatted with commas: <em>20,000.00</em></td></tr>
              <tr><td><code>total_contract_value_dollars</code></td><td>Value with $ sign: <em>$20,000.00</em></td></tr>
              <tr><td><code>total_contract_value_words</code></td><td>Written-out legal form: <em>Twenty Thousand Dollars and No/100</em></td></tr>
              <tr><td><code>start_date</code></td><td>Contract start date</td></tr>
              <tr><td><code>end_date</code></td><td>Contract end date</td></tr>
              <tr><td><code>department_name</code></td><td>Department full name</td></tr>
              <tr><td><code>payment_terms_name</code></td><td>Payment term name (e.g., Net 30)</td></tr>
              <tr><td><code>governing_law</code></td><td>Governing law jurisdiction</td></tr>
              <tr><td><code>account_number</code></td><td>GL account number</td></tr>
              <tr><td><code>po_number</code></td><td>Purchase order number</td></tr>
              <tr><td><code>counterparty_company_name</code></td><td>Counterparty company name</td></tr>
              <tr><td><code>counterparty_contact_name</code></td><td>Primary contact name</td></tr>
              <tr><td><code>counterparty_contact_email</code></td><td>Primary contact email</td></tr>
              <tr><td><code>counterparty_address</code></td><td>Full address as a single line</td></tr>
              <tr><td><code>counterparty_address_line1</code></td><td>Street address</td></tr>
              <tr><td><code>counterparty_city</code></td><td>City</td></tr>
              <tr><td><code>counterparty_state</code></td><td>State</td></tr>
              <tr><td><code>counterparty_postal_code</code></td><td>ZIP / Postal code</td></tr>
              <tr><td><code>counterparty_tax_id</code></td><td>Tax ID / EIN</td></tr>
              <tr><td><code>counterparty_signer1_name</code></td><td>Signer 1 name</td></tr>
              <tr><td><code>counterparty_signer1_title</code></td><td>Signer 1 title</td></tr>
              <tr><td><code>counterparty_signer1_email</code></td><td>Signer 1 email</td></tr>
              <tr><td><code>counterparty_signer2_name</code></td><td>Signer 2 name</td></tr>
              <tr><td><code>counterparty_signer2_title</code></td><td>Signer 2 title</td></tr>
              <tr><td><code>counterparty_signer3_name</code></td><td>Signer 3 name</td></tr>
              <tr><td><code>counterparty_signer3_title</code></td><td>Signer 3 title</td></tr>
              <tr><td><code>exhibit_list</code></td><td>Auto-built list of all documents with exhibit labels (e.g., "Exhibit A - Scope of Work; Exhibit B - COI")</td></tr>
              <tr><td><code>owner_primary_contact_name</code></td><td>Responsible town employee name</td></tr>
            </tbody>
          </table>
        </div>
        <div class="tip-box">Development Agreement contracts expose additional <code>da_*</code> fields for property, developer entity, and hearing date information. See the full Merge Field Reference in the app for the complete list.</div>
      </section>

      <!-- ═══════════════════════════════════════════════════════════════ -->
      <section id="backup">
        <h2>Database Backup</h2>
        <p>Go to <strong>System Settings → Database Backup</strong> to download a full SQL backup of the database.</p>
        <ol>
          <li>Click <strong>Download Backup</strong> to generate and download the backup file immediately.</li>
          <li>Store the downloaded file in a secure location outside the server.</li>
        </ol>
        <div class="warn-box">
          <strong>Important:</strong> The database backup contains sensitive contract and personnel data. Treat it accordingly — store it encrypted and restrict access to authorized staff only.
        </div>
      </section>

      <hr class="mt-5">
      <p class="text-muted small text-center pb-4">
        <?= h($appName) ?> User Manual &mdash; Last updated <?= date('F Y') ?>
      </p>

    </div><!-- /manual-content -->
  </div><!-- /row -->
</div><!-- /container-fluid -->

<!-- Print button -->
<button class="btn btn-outline-secondary btn-sm print-btn shadow" onclick="window.print()">🖨 Print Manual</button>

<script>
// Highlight active TOC link on scroll
(function () {
  const links = document.querySelectorAll('#manualToc .nav-link[href^="#"]');
  const sections = Array.from(links).map(l => document.querySelector(l.getAttribute('href'))).filter(Boolean);

  function onScroll() {
    let current = null;
    sections.forEach(s => {
      if (window.scrollY >= s.offsetTop - 100) current = s.id;
    });
    links.forEach(l => {
      l.classList.toggle('active', l.getAttribute('href') === '#' + current);
    });
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();
})();
</script>

<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>

