document.getElementById('add-talking-point').addEventListener('click', function() {
    // Get the number of current talking points
    var numberOfTalkingPoints = document.querySelectorAll('[id^="talking_point_"]').length;

    // Create new label and input elements
    var newLabel = document.createElement('label');
    newLabel.setAttribute('for', 'talking_point_' + (numberOfTalkingPoints + 1));
    newLabel.textContent = 'Talking Point ' + (numberOfTalkingPoints + 1) + ':';

    var newInput = document.createElement('input');
    newInput.setAttribute('type', 'text');
    newInput.setAttribute('name', 'talking_points[]');
    newInput.setAttribute('id', 'talking_point_' + (numberOfTalkingPoints + 1));

    // Append the new elements to the talking points div
    var talkingPointsDiv = document.getElementById('talking-points');
    talkingPointsDiv.appendChild(newLabel);
    talkingPointsDiv.appendChild(newInput);
    talkingPointsDiv.appendChild(document.createElement('br'));

    // Create "-" button
    var newRemoveButton = document.createElement('button');
    newRemoveButton.innerText = '-';
    newRemoveButton.classList.add('add-remove-button', 'remove-talking-point');
    newRemoveButton.addEventListener('click', function() {
        // Ensure we never go below 3 talking points
        if(document.querySelectorAll('[id^="talking_point_"]').length > 3) {
            var talkingPointDiv = newRemoveButton.closest('.talking-point');
            talkingPointDiv.remove();
        }
    });
    talkingPointsDiv.appendChild(newRemoveButton);
    talkingPointsDiv.appendChild(document.createElement('br'));
});
