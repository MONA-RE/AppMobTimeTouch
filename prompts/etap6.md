procède à l'étape 6 du guide de migration MIGRATION_GUIDE.md


we already made some task in Step 6.1 of the migration guide by 

- extracting the remaining CSS component styles from home.tpl into  Assets/css/timeclock-components.css
- creating a location manager module for handling GPS functionality:(Assets/js/location-manager.js)
- creating a UI components module for modal and form interactions: (Assets/js/ui-components.js)
- creating the responsive CSS file to handle mobile-specific styles:(Assets/css/timeclock-responsive.css)

read those files in order to remove all the remaining inline JavaScript and CSS, and add the closing tags in tpl/home.tpl