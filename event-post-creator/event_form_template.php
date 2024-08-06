 <!-- <link rel='stylesheet' id='dashicons-css' href='<?php //echo plugin_dir_url(__FILE__) . 'css/custom.css'  ?>?ver=<?php echo time() ?>' /> -->
 <div class="container">
        <h3>Event Submission Form</h3>
    <form id="eventForm" enctype="multipart/form-data" method="post">
        <div class="form-group">
            <label for="eventTitle">Event Title:</label>
            <input type="text" id="eventTitle" name="eventTitle" required>
        </div>

        <div class="form-group">
            <label for="eventImage">Image of Event:</label>
            <input type="file" id="eventImage" name="eventImage">
        </div>

        <div class="form-group">
            <label for="eventDate">Date of Event:</label>
            <input type="date" id="eventDate" name="eventDate" required>
        </div>

        <div class="form-group">
            <label for="eventStartTime">Start Time:</label>
            <input type="time" id="eventStartTime" name="eventStartTime" required>
        </div>

        <div class="form-group">
            <label for="eventEndTime">End Time:</label>
            <input type="time" id="eventEndTime" name="eventEndTime" required>
        </div>

        <div class="form-group">
            <label for="eventPrice">Price:</label>
            <input type="number" id="eventPrice" name="eventPrice" required>
        </div>

        <div class="form-group">
            <label for="eventDescription">Event Description:</label>
            <textarea id="eventDescription" name="eventDescription" required></textarea>
        </div>

        <div class="form-group">
            <label>Event Category:</label>
            <div class="checkbox-group">
                <input type="checkbox" id="eventCategory" name="eventCategory[]" value="event">
                <label for="eventCategory">Event</label>
            </div>
        </div>

        <input type="submit" name="submitEvent" class="submit-button" value="Submit">
    </form>
</div>