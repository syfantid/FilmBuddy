package movies;

/**
 * Class to represent a Movie object
 * Created by Sofia on 4/3/2016.
 */
public class Movie {
    private String title;
    private int year;
    private String categories;
    private String synopsis;
    private String iconURL;
    private String cast;
    private String director;
    private String imdbURL;
    private String extendedPlot;

    /**
     * Constructor of the class
     * @param title The movie's title
     * @param year The movie's production year
     * @param categories The categories related to the movie
     * @param synopsis The short synopsis of the movie
     * @param iconURL The movie's icon URL
     * @param cast The movie's cast
     * @param director The movie's director
     * @param imdbURL The movie's IMDb page URL
     * @param extendedPlot The movie's extended plot
     */
    public Movie(String title, int year, String categories, String synopsis, String iconURL, String cast,
                 String director, String imdbURL, String extendedPlot) {
        this.title = title;
        this.year = year;
        this.categories = categories;
        this.synopsis = synopsis;
        this.iconURL = iconURL;
        this.cast = cast;
        this.director = director;
        this.imdbURL = imdbURL;
        this.extendedPlot = extendedPlot;
    }

    /**
     * Gets the title of the movie
     * @return The movie's title
     */
    public String getTitle() {
        return title;
    }

    /**
     * Gets the production year of the movie
     * @return The movie's production year
     */
    public int getYear() {
        return year;
    }

    /**
     * Gets the categories related to the movie
     * @return The related categories in a String separated by commas
     */
    public String getCategories() {
        return categories;
    }

    /**
     * Gets the short synopsis of the movie
     * @return The movie's short synopsis
     */
    public String getSynopsis() {
        return synopsis;
    }

    /**
     * Gets the icon URL of the movie
     * @return The movie's icon URL
     */
    public String getIconURL() {
        return iconURL;
    }

    /**
     * Gets the cast of the movie
     * @return The movie's cast in a String separated by commas
     */
    public String getCast() {
        return cast;
    }

    /**
     * Gets the director of the movie
     * @return The movie's director
     */
    public String getDirector() {
        return director;
    }

    /**
     * Gets the IMDb URL of the movie
     * @return The movie's IMDb URL
     */
    public String getImdbURL() {
        return imdbURL;
    }

    /**
     * Gets the extended plot of the movie
     * @return The movie's extended plot
     */
    public String getExtendedPlot() {
        return extendedPlot;
    }
}
