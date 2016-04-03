package movies;

import jwiki.core.NS;
import jwiki.core.Wiki;

import java.util.ArrayList;
import java.util.Iterator;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

/**
 * Created by Sofia on 3/31/2016.
 */
public class MovieCollector {

    /**
     * Gets the extended plot of a Wikipedia film page
     * @param page The text of the Wikipedia film page
     * @return The extended plot of the film
     */
    private static String getExtendedPlot(String page) {
        String plot;
        int index1 = page.indexOf("==Plot=="); // The index of the Plot title in the page
        int index2; // The index of the Cast title in the page

        if(page.contains("==Cast==")) { // Cast title is "Cast"
            index2 = page.indexOf("==Cast==");
            if(index1 < index2) {
                plot = page.substring(index1 + "==Plot==".length(),
                        index2);
            } else { // In a few articles the cast section precedes the plot section, thus not marking its ending
                plot = "";
            }
        } else if(page.contains("==Principal cast==")) { // Cast title is "Principal Cast"
            index2 = page.indexOf("==Principal cast==");
            plot = page.substring(index1 + "==Plot==".length(),
                    index2);
        } else if(page.contains("==Main cast==")){ // Cast title is "Main Cast"
            index2 = page.indexOf("==Main cast==");
            plot = page.substring(index1 + "==Plot==".length(),
                    index2);
        } else { // There's no cast section to mark the ending of plot section
            plot = "";
        }

        return plot;
    }

    /**
     * Given a Wikipedia Category name it strips the year
     * @param category The Wikipedia Category name; example format "Category:2000 films'
     * @return The production year of the films in the given category
     */
    private static String getYear(String category) {
        String year = category.substring(category.indexOf("Category:") + "Category:".length(),
                category.indexOf(" films"));
        return year;
    }

    /**
     * Gets the director of a film
     * @param page The film's Wikipedia page text
     * @return The name of the director
     */
    private static String getDirector(String page) {
        Matcher m = Pattern.compile("director = ([\\w\\s]*)").matcher(page);
        if(m.find()) {
            return m.group(1);
        }
        return "";
    }

    /**
     * Gets the main actors and actresses of a film
     * @param page The film's Wikipedia page text
     * @return The name of the director
     */
    private static String getStars(String page) {
        String stars = "";
        Matcher m = Pattern.compile("starring = (\\[\\[[\\w\\s]*\\]\\](<br>)?)*").matcher(page);
        if(m.find()) {
            stars = m.group();
            stars = stars.replaceAll("<br>",", ").replaceAll("[\\[\\]]","").replace("starring = ","");
        }
        return stars;
    }

    /**
     * Gets the synopsis of a film
     * @param page The film's Wikipedia page text
     * @return The synopsis of the film
     */
    private static String getSynopsis(String page) {
        String synopsis = "";
        Matcher m = Pattern.compile("}} [\\w\\d\\s.,\\/#!$%&;:{}=\\-_`'\"~()]* ==").matcher(page);
        if(m.find()) {
            synopsis = m.group();
            synopsis = synopsis.replaceAll("'{2,}","\"").replace("}}","").replace("==","");
        }
        return synopsis;

    }

    public static void main(String[] args) throws Throwable
    {
        Wiki wiki = new Wiki("syfantid", "sofia24041994", "en.wikipedia.org"); // Login to Wikipedia

        // Numbered are the fields that will be stored in the DB
        // TODO: 3/31/2016 Get all the categories
        String category = "Category:2000 films";
        String year = getYear(category); // 1. The year for all the movies in the specific category
        ArrayList<String> films = wiki.getCategoryMembers(category,NS.MAIN); // Get all the articles in this category


        // THIS IS A TESTING PART
        String titleTest = films.get(200); // Film title
        String pageTestFormatted = wiki.getPageText(titleTest); // The text of the article
        String pageTest = pageTestFormatted.replaceAll("\\s+", " ");
        String extendedPlotTest = getExtendedPlot(pageTest); // The extended plot of the film
        if(!extendedPlotTest.isEmpty()) {
            System.out.println("Page for film: " + titleTest);
            //System.out.println(pageTest);
            System.out.println(getSynopsis(pageTest));
        }

        // THIS IS THE NORMAL CODE
        /*Iterator<String> it = films.iterator();
        while (it.hasNext()) {
            String f = it.next();
            if(f.startsWith("List of")) { // Categories include lists of special films, which will be excluded
                it.remove();
            } else {
                String title = f; // 2. Film title
                String pageFormatted = wiki.getPageText(title); // The text of the article
                String page = pageFormatted.replaceAll("\\s+", " "); // Remove any extra whitespaces
                String extendedPlot = getExtendedPlot(page); // 3. The extended plot of the film
                if(!extendedPlot.isEmpty()) { // Exclude films with empty Wikipedia pages
                    // Get all the extra information needed to present the film
                }
            }
        }*/
    }
}
