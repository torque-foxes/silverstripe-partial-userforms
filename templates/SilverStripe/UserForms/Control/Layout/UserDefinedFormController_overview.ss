<div class="container">
    <h1>$Title</h1>

    <p>$FormOverview</p>
    $Form

    <% if $FormLocked %>
        <h3>Form locked</h3>
        <p>This form is currently being used by someone else. Please try again in 30 minutes.</p>
    <% end_if %>
</div>
