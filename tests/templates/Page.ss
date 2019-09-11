<!DOCTYPE html>
<html>
    <head></head>
    <body>
        <h1>$Title</h1>

        $Content
        $Form

        <% if $FormLocked %>
            <p>This form is currently being used by someone else. Please try again in 30 minutes.</p>
        <% end_if %>
    </body>
</html>
