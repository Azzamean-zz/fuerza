# ==============================================================================
# GENERAL CONFIGURATION
#
# View all options here:
# http://compass-style.org/help/tutorials/configuration-reference/
# ==============================================================================

# Require any additional compass plugins here.
require "sass-globbing"

# You can select your preferred output style here (can be overridden via the command line):
# output_style = :expanded or :nested or :compact or :compressed

# Enable relative paths to assets via compass helper functions.
relative_assets = true
sourcemap = true
output_style = :compressed

# ==============================================================================
# COMPASS DIRECTORY CONFIGURATION
# ==============================================================================

# The root of all operations, must be set for Compass to work.
http_path             = "/"

# Directory containing the SASS source files
sass_dir              = "sass"

# Directory where Compass dumps the generated CSS files
css_dir               = "css"