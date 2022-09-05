# Open Graph Plugin
This plugin gives Joomla content creators the ability to customize their article previews when it’s being shared on social networking sites. 
It provides them the flexibility to modify each article's open graph data, which in turn can be used by social media platforms to display rich objects. 
Some of these parameters include the following
- Article Type
- Article Title
- Article Description
- Article Image
- Published Date
- Article Author

By default (when none of the open graph parameters have been set), the article's parameters are considered as its open graph parameters.
Modifications to any of the open graph fields will override the article's default parameters.
This behavior can be seen for all the parameters defined above.

For images, a slightly different hierarchy is observed. 
- Open Graph Image (Highest Priority)
- Full Article Image (Lower Priority)
- Intro Image (Lowest Priority)

Here’s how Facebook uses open graph data when an article is shared.

![image](https://user-images.githubusercontent.com/84401192/188490348-88efa252-03ed-49fa-a4f2-dcd0d6c6903f.png)

Move to the article's frontend and click on ```View page source```. Here's what it'll show.

![image](https://user-images.githubusercontent.com/84401192/188488668-9969b6d4-8c29-453b-a489-ac558db7a9d5.png)

Here’s the screen where you can set those parameters in the article's backend.

![image](https://user-images.githubusercontent.com/84401192/188490158-a5ad392a-d83c-499a-b023-2bf10c3f69c1.png)


## Installation and Testing
<ol>
  <li> Download the zip file </li>
  <li> Go to Joomla's administration panel of your site </li>
  <li> Under system settings, click on install extensions </li>
  <li> Upload the downloaded zip file </li>
  <li> Click on manage extensions and enable the plugin</li>
  <li> Now move to the <i>Article Edit View</i> for any article</li>
  <li> You will notice a new <i>Open Graph</i> tab</li>
</ol>
