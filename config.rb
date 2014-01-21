# # Require any additional compass plugins here.

project_type = :stand_alone
# Set this to the root of your project when deployed:
http_path = "/signaturegenerator"
css_dir = "css"
sass_dir = "scss"
images_dir = "images"

output_style = :expanded
# output_style = :compressed
environment    = :production

# To enable relative paths to assets via compass helper functions. Uncomment:
relative_assets = true

# disable comments in the output. We want admin comments
# to be verbose 
line_comments = false

asset_cache_buster :none