package content_analyzer;

import movies_component.MovieStoragerMongo;
import movies_component.MovieStoragerSQL;
import org.json.JSONObject;

import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.ArrayList;

/**
 * Class to analyze and populate the dataset with parsed and semantics plots
 * Created by Sofia on 4/8/2016.
 */
public class ContentAnalyzer {
    private static MovieStoragerSQL storagerSQL;
    private static MovieStoragerMongo storagerMongo;
    private static int i;

    static {
        storagerSQL = new MovieStoragerSQL();
        storagerMongo = new MovieStoragerMongo("localhost",27017);
        i = 1;
    }

    /**
     * Gets all the movie IDs from the Database
     * @return A list of all the IDs
     * @throws SQLException In case the SQL query fails
     */
    private static ArrayList<String> getMovieIDs() throws SQLException {
        ArrayList<String> ids = new ArrayList<>();

        // The mysql insert statement
        String query = " SELECT id FROM `all_movies` ";

        // Execute the PreparedStatement
        ResultSet rs = storagerSQL.selectQuery(query);
        while (rs.next()) {
            ids.add(rs.getString("id"));
        }
        return ids;
    }


    private static void migrateFromMongoToMySQL() throws SQLException {
        ArrayList<String> ids = getMovieIDs(); // Gets all IDs from MySQL DB

        // Fields to be migrated
        String icon;
        String genre;
        Double rating;
        String countries;

        for(String id:ids) {
            JSONObject movie = storagerMongo.getMovie(id);
            if(movie.length() != 0) { // If movie exists in Mongo database
                icon = movie.getJSONObject("movie").getString("poster");
                genre = movie.getJSONObject("movie").getString("genre");
                try {
                    rating = Double.parseDouble(movie.getJSONObject("movie").getString("imdbRating"));
                } catch(Exception e) { // In case there is no IMDB rating available ("N/A")
                    rating = null;
                }
                countries = movie.getJSONObject("movie").get("countries").toString();

                storagerSQL.insertField(id,icon,"icon");
                storagerSQL.insertField(id,genre,"genre");
                storagerSQL.insertField(id,rating,"imdb_rating");
                storagerSQL.insertField(id,countries,"countries");
            }
        }

    }

    /**
     * Populates the semantics_plot column of the Database
     * @throws SQLException In case an SQL query fails
     */
    private static void cleanAndInsertSemantics() throws SQLException {
        ArrayList<String> ids = getMovieIDs(); // Get the IDs of all the movies
       for(String id : ids) { // for each film
           System.out.println(i++ + ". Working on movie with ID: " + id);
           // If plot is null then populate it, otherwise it has already been processed
           if (storagerSQL.checkIfPlotIsNull(id)) {
               // Fetch the extended plot
               String query = "SELECT `extended_plot` FROM `all_movies` WHERE `id`=" + id;
               ResultSet rs = storagerSQL.selectQuery(query);
               // "Clear" the extended plot
               rs.next();
               String extendedPlot = rs.getString(1);
               extendedPlot = clearText(extendedPlot);
               if (extendedPlot.isEmpty()) { // There is no extended plot; Handles problem
                   // Delete movies with no extended plot
                   storagerMongo.deleteMovie(id);
                   storagerSQL.deleteMovie(id);
                   // Insert parsed plot into MySQL DB
               } else {
                   storagerSQL.insertParsedPlot(id, extendedPlot);
                   // Find the semantics plot
                   String semantics = SemanticsExtractor.findSemantics(extendedPlot);
                   // Insert the semantics plot in the Database
                   storagerSQL.insertSemanticPlot(id, semantics);
                   System.out.println("\nID: " + id + " Extended Plot: " + extendedPlot + "\nSemantics plot: " + semantics);
               }
           } else {
               System.out.println("Movie has been already parsed for semantic relatedness!");
           }
       }
    }

    /**
     * "Clears" text from unnecessary punctuation/stop-words/nouns etc.
     * @param text The text to be "cleared"
     * @return The "cleared" text
     */
    private static String clearText(String text) {
        return Processor.preprocess(text);
    }

    /**
     * Main funtion that performs the content analysis
     * @param args None needed
     * @throws SQLException In case the connection fails
     */
    public static void main(String[] args) throws SQLException {
        //cleanAndInsertSemantics();
        migrateFromMongoToMySQL();
        if(!storagerSQL.closeConnection()) {
            System.out.println("Failed to close the connection!");
        }
    }
}
