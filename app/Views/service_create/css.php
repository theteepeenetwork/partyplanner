<style>
    /* ------------------------------
BASE STYLES (applies to all)
------------------------------ */
    html,
    body {
        margin: 0;
        padding: 0;
        font-family: sans-serif;
    }

    main {
        margin: 4rem;
        padding-top: 25px;
    }

    section {
        padding: 20px;
        margin-bottom: 30px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background-color: #f9f9f9;
        margin-top: 2rem;
    }

    section h4 {
        font-size: 1.5em;
        margin-bottom: 15px;
        border-bottom: 2px solid #007bff;
        padding-bottom: 10px;
        color: #007bff;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .divider {
        height: 1px;
        background-color: #ddd;
        margin: 20px 0;
    }

    /* Info icon style */
    .info-icon {
        display: inline-block;
        width: 18px;
        height: 18px;
        text-align: center;
        line-height: 18px;
        font-size: 12px;
        font-weight: bold;
        border-radius: 50%;
        background: #007bff;
        color: #fff;
        cursor: pointer;
        margin-left: 5px;
        position: relative;
    }

    .info-icon:hover {
        background: #0056b3;
    }

    /* The modal box */
    .my-modal {
        position: fixed;
        /* keep the modal fixed in the viewport */
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 90%;
        /* flexible width for small screens */

        /* don’t exceed 500px on larger devices */
        max-height: 80vh;
        /* limit modal height */
        overflow-y: auto;
        /* allow scroll if content is too tall */
        -webkit-overflow-scrolling: touch;
        background: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        border-radius: 8px;
        padding: 20px;
        z-index: 1000;
        /* above overlay */
    }

    .my-modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 999;
        display: none;
        /* hide by default */
    }

    .close-btn {
        cursor: pointer;
        float: right;
        font-size: 18px;
        font-weight: bold;
    }

    /* Tag input styling */
    .tag-container {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        border: 1px solid #ccc;
        padding: 5px;
        min-height: 40px;
        border-radius: 5px;
        cursor: text;
    }

    .tag {
        background-color: #e9ecef;
        border-radius: 3px;
        padding: 2px 8px;
        margin: 2px;
        display: inline-flex;
        align-items: center;
    }

    .tag .remove-tag {
        margin-left: 6px;
        cursor: pointer;
        font-weight: bold;
    }

    .tag-input {
        border: none;
        outline: none;
        flex: 1;
        min-width: 100px;
        margin: 5px;
    }

    /* Basic error styling from server-side validation */
    .is-invalid {
        border: 1px solid red;
    }

    .invalid-feedback {
        color: red;
        font-size: 0.875rem;

    }

    .custom-tooltip {
        position: absolute;
        background-color: #333;
        color: #fff;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 12px;
        white-space: nowrap;
        display: yes;
        z-index: 1000;
    }

    /* Pricing Section styling */


    .pricing-section h4 {
        margin-bottom: 1rem;
        color: #007bff;
        font-size: 1.25rem;
        border-bottom: 1px solid #ccc;
        padding-bottom: 0.25rem;
    }

    .input-group {
        display: flex;

        /* Stack rows vertically */
        align-items: flex-start;
        /* Align items to the start (left-align) */
        width: 100%;
        /* Ensure it spans the full container width */
        flex-wrap: nowrap;
    }

    .input-group .form-row {
        align-items: center;
        /* Center align vertically */
        width: 100%;
        /* Each row takes up the full width */
        margin-bottom: 10px;
        /* Add spacing between rows */
        flex-wrap: nowrap;
    }

    .input-group .form-row .input-group-text {

        /* Prevent the label from shrinking */
        margin-right: 10px;
        /* Add spacing after the label */
    }

    .input-group .form-row .form-control {

        /* Allow the input to expand and fill available space */
        min-width: 70px;
        /* Prevent the input from becoming too small */
        max-width: 100%;
        /* Ensure it doesn’t exceed the container width */
    }

    .pitch-group {
        border: 1px solid lightgray;
        border-radius: 5px;
        margin-bottom: 10px;
        padding: 10px;
        align-items: center;
    }

    @media (min-width: 751px) {
        .input-group {

            /* Arrange items in a single row on wider screens */

            /* Prevent wrapping */

        }

        .input-group .form-row {
            flex: 1;
            /* Allow rows to grow equally */

            /* Add spacing between rows */
            margin-bottom: 0;
            /* Remove bottom margin in row layout */
        }

        .input-group .form-row:last-child {
            margin-right: 0;
            /* Remove spacing after the last row */
        }
    }

    .all-rows-container {
        display: flex;
        flex-wrap: wrap;
        /* Enables wrapping on smaller screens */
        gap: 10px;
        /* Adds spacing between input groups */
        align-items: center;
        /* Aligns items vertically for consistency */
    }

    .input-group {
        flex: 1;
        /* Ensures inputs take equal space */
        min-width: 150px;
        /* Prevents inputs from becoming too narrow */
        max-width: 300px;
        /* Optional: Controls maximum width on larger screens */
    }

    @media (max-width: 768px) {
        .all-rows-container {
            flex-direction: column;
            /* Stacks items vertically on smaller screens */
        }

        .input-group {
            max-width: none;
            /* Allows full-width on mobile */
        }
    }







    /* Buttons */


    .btn-primary {
        background-color: #007bff;
        color: #fff;
        border-color: #007bff;
    }

    .btn-primary:hover {
        background-color: #0056b3;
    }

    .btn-danger {
        background-color: #dc3545;
        color: #fff;
        border-color: #dc3545;
        margin-top: 0 !important;
        margin-left: 10px;
    }

    .btn-danger:hover {
        background-color: #b52d38;
    }

    /* Default Remove Button */
    .remove-guest-range {
        background-color: rgb(146, 32, 44);
        color: #fff;
        border: none;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 14px;
        cursor: pointer;
    }



    /*
all-rows-container:
By default, we stack sub-divs on narrower screens
but we go to a single row if screen is 751px or wider
*/

    /* For screens 751px and up: place all sub-divs horizontally in a row */


    /*
GUEST-BASED PRICING FOR SMALL DEVICES:
Under 500px: each sub-block on its own line
*/
</style>